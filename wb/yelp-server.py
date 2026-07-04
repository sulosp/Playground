"""Serve the Yelp reviews widget and API for local or remote embedding.

Run:  python yelp-server.py
Open: http://localhost:8787/elfsight-widget.html

Embed on external sites (after deploying to a public HTTPS host):
  <script src="https://YOUR-HOST/embed.js" async></script>
  <div class="mdg-yelp-widget" data-height="480"></div>

Or iframe:
  <iframe src="https://YOUR-HOST/embed.html" width="100%" height="480"
          frameborder="0" style="border:none;" title="Yelp Reviews"></iframe>

Optional env vars:
  HOST          - bind address (default 0.0.0.0)
  PORT          - port (default 8787)
  PUBLIC_URL    - public base URL shown in startup logs
  YELP_BIZ_ID   - encBizId from the Yelp page meta tag (yelp-biz-id)
  YELP_API_KEY  - Yelp Fusion API key (fallback, max 3 reviews)
"""

from __future__ import annotations

import json
import os
import re
import sys
from base64 import b64encode
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import urlparse

import requests
YELP_URL = "https://www.yelp.com/biz/mobile-dog-grooming-irvine-2"
YELP_ALIAS = "mobile-dog-grooming-irvine-2"
GQL_URL = "https://www.yelp.com/gql/batch"
DOC_ID = "ef51f33d1b0eccc958dddbf6cde15739c48b34637a00ebe316441031d4bf7681"
ROOT = Path(__file__).resolve().parent
CONFIG_PATH = ROOT / "yelp-config.json"
REVIEWS_JSON_PATH = ROOT / "yelp-reviews.json"

HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/121.0.0.0 Safari/537.36"
    ),
    "Accept-Language": "en-US,en;q=0.9",
}


def load_config() -> dict:
    if not CONFIG_PATH.exists():
        return {}
    try:
        return json.loads(CONFIG_PATH.read_text(encoding="utf-8"))
    except json.JSONDecodeError:
        return {}


def extract_enc_biz_id(html: str) -> str | None:
    if env_id := os.environ.get("YELP_BIZ_ID"):
        return env_id.strip()

    config = load_config()
    if config_id := config.get("bizId"):
        return str(config_id).strip()

    meta_match = re.search(
        r'<meta[^>]+name=["\']yelp-biz-id["\'][^>]+content=["\']([^"\']+)["\']',
        html,
        re.I,
    )
    if meta_match:
        return meta_match.group(1)

    for pattern in (
        r'"encid":"([^"]+)"',
        r'"encBizId":"([^"]+)"',
        r'"bizEncId":"([^"]+)"',
    ):
        if match := re.search(pattern, html):
            return match.group(1)
    return None


def fetch_page(session: requests.Session) -> requests.Response:
    return session.get(
        YELP_URL,
        headers={
            **HEADERS,
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Referer": "https://www.google.com/",
        },
        timeout=30,
    )


def fetch_reviews_page(
    session: requests.Session, enc_biz_id: str, offset: int = 0
) -> dict:
    variables = {
        "encBizId": enc_biz_id,
        "reviewsPerPage": 10,
        "selectedReviewEncId": "",
        "hasSelectedReview": False,
        "sortBy": "DATE_DESC",
        "languageCode": "en",
        "ratings": [5, 4, 3, 2, 1],
        "isSearching": False,
        "isTranslating": False,
        "translateLanguageCode": "en",
        "reactionsSourceFlow": "businessPageReviewSection",
        "minConfidenceLevel": "HIGH_CONFIDENCE",
        "highlightType": "",
        "highlightIdentifier": "",
        "isHighlighting": False,
    }
    if offset:
        variables["after"] = b64encode(
            json.dumps({"version": 1, "type": "offset", "offset": offset}).encode()
        ).decode()

    payload = [
        {
            "operationName": "GetBusinessReviewFeed",
            "variables": variables,
            "extensions": {"operationType": "query", "documentId": DOC_ID},
        }
    ]

    response = session.post(
        GQL_URL,
        headers={
            **HEADERS,
            "Accept": "application/json",
            "Content-Type": "application/json",
            "Origin": "https://www.yelp.com",
            "Referer": YELP_URL,
            "x-apollo-operation-name": "GetBusinessReviewFeed",
        },
        json=payload,
        timeout=30,
    )
    response.raise_for_status()
    data = response.json()
    return data[0] if isinstance(data, list) else data


def format_review_date(node: dict) -> str:
    created = node.get("createdAt") or {}
    raw = (
        node.get("localizedDate")
        or created.get("localDateTimeForReview")
        or created.get("utcDateTime")
        or ""
    )
    if not raw:
        return ""
    if "T" in raw:
        return raw.split("T")[0]
    return raw


def parse_gql_reviews(data: dict) -> tuple[list[dict], float | None, int | None]:
    business = (data.get("data") or {}).get("business") or {}
    rating = business.get("rating")
    review_count = business.get("reviewCount")

    reviews = []
    edges = (business.get("reviews") or {}).get("edges") or []

    for edge in edges:
        node = edge.get("node") or {}
        author = node.get("author") or {}
        text = node.get("text") or {}
        photo_url = None
        photo = author.get("profilePhoto") or {}
        if photo_url_obj := photo.get("photoUrl"):
            photo_url = photo_url_obj.get("userSrc") or photo_url_obj.get("url")

        name = author.get("displayName") or "Yelp User"
        reviews.append(
            {
                "name": name,
                "initial": name.strip()[0].upper() if name.strip() else "?",
                "date": format_review_date(node),
                "rating": node.get("rating") or 5,
                "text": text.get("full") or text.get("translated") or "",
                "photoUrl": photo_url,
            }
        )

    return reviews, rating, review_count


def fetch_via_fusion(session: requests.Session) -> dict:
    api_key = os.environ.get("YELP_API_KEY")
    if not api_key:
        raise RuntimeError("Yelp page blocked and YELP_API_KEY is not set")

    headers = {"Authorization": f"Bearer {api_key}"}
    biz = session.get(
        f"https://api.yelp.com/v3/businesses/{YELP_ALIAS}",
        headers=headers,
        timeout=30,
    )
    biz.raise_for_status()
    biz_data = biz.json()

    rev = session.get(
        f"https://api.yelp.com/v3/businesses/{YELP_ALIAS}/reviews",
        headers=headers,
        timeout=30,
    )
    rev.raise_for_status()
    rev_data = rev.json()

    reviews = []
    for item in rev_data.get("reviews") or []:
        user = item.get("user") or {}
        name = user.get("name") or "Yelp User"
        reviews.append(
            {
                "name": name,
                "initial": name.strip()[0].upper() if name.strip() else "?",
                "date": item.get("time_created", "")[:10],
                "rating": item.get("rating") or 5,
                "text": item.get("text") or "",
                "photoUrl": user.get("image_url"),
            }
        )

    return {
        "source": YELP_URL,
        "rating": biz_data.get("rating"),
        "reviewCount": biz_data.get("review_count"),
        "reviews": reviews,
    }


def fetch_yelp_reviews() -> dict:
    session = requests.Session()
    config = load_config()
    enc_biz_id = os.environ.get("YELP_BIZ_ID") or config.get("bizId")

    if enc_biz_id:
        enc_biz_id = str(enc_biz_id).strip()
        all_reviews: list[dict] = []
        rating = config.get("rating")
        review_count = config.get("reviewCount")

        for offset in range(0, 100, 10):
            data = fetch_reviews_page(session, enc_biz_id, offset)
            batch, rating, review_count = parse_gql_reviews(data)
            if not batch:
                break
            all_reviews.extend(batch)
            if len(batch) < 10:
                break

        if all_reviews:
            return {
                "source": YELP_URL,
                "rating": rating,
                "reviewCount": review_count,
                "reviews": all_reviews,
            }

    page = fetch_page(session)

    if page.status_code == 403:
        raise RuntimeError(
            "Yelp blocked the server request. Open the Yelp page in your browser, "
            "view page source, search for yelp-biz-id, and save it to yelp-config.json "
            'as {"bizId": "YOUR_ID"}. Or set YELP_API_KEY for the official Yelp API.'
        )

    page.raise_for_status()
    enc_biz_id = extract_enc_biz_id(page.text)
    if not enc_biz_id:
        raise RuntimeError(
            "Could not find yelp-biz-id on the Yelp page. Add it to yelp-config.json "
            'as {"bizId": "YOUR_ID"}.'
        )

    all_reviews = []
    rating = None
    review_count = None

    for offset in range(0, 100, 10):
        data = fetch_reviews_page(session, enc_biz_id, offset)
        batch, rating, review_count = parse_gql_reviews(data)
        if not batch:
            break
        all_reviews.extend(batch)
        if len(batch) < 10:
            break

    if not all_reviews:
        return fetch_via_fusion(session)

    return {
        "source": YELP_URL,
        "rating": rating,
        "reviewCount": review_count,
        "reviews": all_reviews,
    }


def export_reviews_json() -> None:
    try:
        payload = fetch_yelp_reviews()
        REVIEWS_JSON_PATH.write_text(
            json.dumps(payload, indent=2, ensure_ascii=False),
            encoding="utf-8",
        )
        print(f"Exported {len(payload.get('reviews', []))} reviews to yelp-reviews.json")
    except Exception as exc:
        print(f"Could not export yelp-reviews.json: {exc}", file=sys.stderr)


class YelpHandler(SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=str(ROOT), **kwargs)

    def end_headers(self):
        path = urlparse(self.path).path
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type")
        if path in ("/embed.html", "/embed.js", "/widget.js", "/widget.css"):
            self.send_header("Content-Security-Policy", "frame-ancestors *")
        super().end_headers()

    def do_OPTIONS(self):
        self.send_response(204)
        self.end_headers()

    def do_GET(self):
        parsed = urlparse(self.path)
        if parsed.path == "/api/yelp-reviews":
            self.handle_reviews()
            return
        super().do_GET()

    def handle_reviews(self):
        try:
            payload = fetch_yelp_reviews()
            body = json.dumps(payload, ensure_ascii=False).encode("utf-8")
            self.send_response(200)
            self.send_header("Content-Type", "application/json; charset=utf-8")
            self.send_header("Cache-Control", "public, max-age=300")
            self.end_headers()
            self.wfile.write(body)
        except Exception as exc:
            if REVIEWS_JSON_PATH.exists():
                body = REVIEWS_JSON_PATH.read_bytes()
                self.send_response(200)
                self.send_header("Content-Type", "application/json; charset=utf-8")
                self.send_header("Cache-Control", "public, max-age=300")
                self.end_headers()
                self.wfile.write(body)
                print(f"Served cached yelp-reviews.json after live fetch failed: {exc}", file=sys.stderr)
                return

            message = str(exc)
            body = json.dumps({"error": message}).encode("utf-8")
            self.send_response(502)
            self.send_header("Content-Type", "application/json; charset=utf-8")
            self.end_headers()
            self.wfile.write(body)
            print(f"Review fetch failed: {message}", file=sys.stderr)

    def log_message(self, format, *args):
        if args and isinstance(args[0], str) and "200" in str(args):
            return
        super().log_message(format, *args)


def main() -> None:
    if "--export" in sys.argv:
        export_reviews_json()
        return

    host = os.environ.get("HOST", "0.0.0.0")
    port = int(os.environ.get("PORT", "8787"))
    public_url = os.environ.get("PUBLIC_URL", f"http://localhost:{port}")

    export_reviews_json()

    server = ThreadingHTTPServer((host, port), YelpHandler)
    print(f"Serving widget on {public_url}/embed.html")
    print(f"Embed script: {public_url}/embed.js")
    print(f"Reviews API:  {public_url}/api/yelp-reviews")
    print(f"Static JSON:  {public_url}/yelp-reviews.json")
    print(f"Yelp source:  {YELP_URL}")
    print("\nPaste into external site editor:")
    print(f'<script src="{public_url}/embed.js" async></script>')
    print('<div class="mdg-yelp-widget" data-height="480"></div>')
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\nStopped.")


if __name__ == "__main__":
    main()
