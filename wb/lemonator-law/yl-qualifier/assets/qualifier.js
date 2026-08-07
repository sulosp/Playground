(function () {
  "use strict";

  function ready(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }

  function initForm(form) {
    if (!form || form.dataset.ylQualifierInit === "1") return;
    form.dataset.ylQualifierInit = "1";
    form.setAttribute("novalidate", "novalidate");

    function resolveEndpoints(config) {
      var apiLink = document.querySelector('link[rel="https://api.w.org/"]');
      var siteRoot = "";

      if (apiLink && apiLink.href) {
        siteRoot = apiLink.href.replace(/\/wp-json\/?$/, "");
      } else if (window.location.origin) {
        siteRoot = window.location.origin;
      }

      if (!config.postUrl && siteRoot) {
        config.postUrl = siteRoot + "/wp-admin/admin-post.php";
      }
      if (!config.ajaxUrl && siteRoot) {
        config.ajaxUrl = siteRoot + "/wp-admin/admin-ajax.php?action=yl_qualifier_submit";
      }
      if (!config.restUrl && apiLink && apiLink.href) {
        config.restUrl = apiLink.href.replace(/\/?$/, "") + "/yl-qualifier/v1/submit";
      }
      if (!config.frontUrl && siteRoot) {
        config.frontUrl = siteRoot + "/?yl_qualifier_submit=1";
      }

      return config;
    }

    var wpConfig = resolveEndpoints({
      ajaxUrl:
        form.dataset.ajaxUrl ||
        (window.ylQualifier && window.ylQualifier.ajaxUrl) ||
        "",
      restUrl:
        form.dataset.restUrl ||
        (window.ylQualifier && window.ylQualifier.restUrl) ||
        "",
      postUrl:
        form.dataset.postUrl ||
        (window.ylQualifier && window.ylQualifier.postUrl) ||
        "",
      frontUrl:
        form.dataset.frontUrl ||
        (window.ylQualifier && window.ylQualifier.frontUrl) ||
        "",
      nonce:
        form.dataset.nonce ||
        (window.ylQualifier && window.ylQualifier.nonce) ||
        "",
      restNonce:
        form.dataset.restNonce ||
        (window.ylQualifier && window.ylQualifier.restNonce) ||
        ""
    });
    var TOTAL_STEPS = 4;
    var currentStep = 1;

    var stepLabels = {
      1: "Vehicle Type",
      2: "Acquisition & issue",
      3: "Repair history",
      4: "Contact Info"
    };

    var vehicleTypeLabels = {
      car: "Car",
      "truck-suv": "Truck / SUV",
      motorcycles: "Motorcycles",
      boats: "Boats",
      aircraft: "Aircraft",
      rvs: "RVs"
    };

    var stepLabelEl = form.querySelector("[data-step-label]");
    var stepNameEl = form.querySelector("[data-step-name]");
    var progressFill = form.querySelector("[data-progress-fill]");
    var progressBar = form.querySelector("[role='progressbar']");
    var formTitle = form.querySelector("[data-form-title]");
    var formSubtitle = form.querySelector("[data-form-subtitle]");
    var steps = form.querySelectorAll(".yl-qualifier__step");
    var backBtn = form.querySelector("[data-back]");
    var nextBtn = form.querySelector("[data-next]");
    var submitBtn = form.querySelector("[data-submit]");
    var successPanel = form.querySelector("[data-success-panel]");
    var statusEl = form.querySelector("[data-form-status]");
    var referenceText = form.querySelector("[data-reference-text]");
    var successSummary = form.querySelector("[data-success-summary]");
    var copyBtn = form.querySelector("[data-copy-reference]");
    var restartBtn = form.querySelector("[data-restart-form]");

    if (!steps.length || !nextBtn || !backBtn || !submitBtn) return;

    function syncRadioGroup(name, selector, selectedClass) {
      form.querySelectorAll('input[name="' + name + '"]').forEach(function (input) {
        var opt = input.closest(selector);
        if (opt) opt.classList.toggle(selectedClass, input.checked);
      });
    }

    function bindRadioOptions(selector, selectedClass) {
      form.querySelectorAll(selector).forEach(function (option) {
        var radio = option.querySelector('input[type="radio"]');
        if (!radio) return;

        if (radio.checked) option.classList.add(selectedClass);

        option.addEventListener("click", function () {
          radio.checked = true;
          syncRadioGroup(radio.name, selector, selectedClass);
        });

        radio.addEventListener("change", function () {
          syncRadioGroup(radio.name, selector, selectedClass);
        });
      });
    }

    bindRadioOptions(".yl-qualifier__vehicle-option", "is-selected");
    bindRadioOptions(".yl-qualifier__choice-option:not(.yl-qualifier__vehicle-option)", "is-selected");
    bindRadioOptions(".yl-qualifier__scale-option", "is-selected");

    function getStepPanel(step) {
      return form.querySelector('.yl-qualifier__step[data-step="' + step + '"]');
    }

    function getStepError(step) {
      return form.querySelector('[data-step-error="' + step + '"]');
    }

    function clearStepErrors(step) {
      var panel = getStepPanel(step);
      if (!panel) return;
      panel.querySelectorAll(".is-invalid").forEach(function (el) {
        el.classList.remove("is-invalid");
      });
      var error = getStepError(step);
      if (error) error.classList.remove("is-visible");
    }

    function validateStep(step) {
      clearStepErrors(step);
      var panel = getStepPanel(step);
      if (!panel) return true;
      var valid = true;
      var seenRadio = {};

      panel.querySelectorAll("input, select, textarea").forEach(function (field) {
        if (field.type === "hidden") return;
        if (field.type === "radio") {
          if (seenRadio[field.name]) return;
          seenRadio[field.name] = true;
          var group = panel.querySelectorAll('input[name="' + field.name + '"]');
          var checked = Array.prototype.some.call(group, function (input) {
            return input.checked;
          });
          if (group[0] && group[0].required && !checked) valid = false;
          return;
        }
        if (field.required && !field.checkValidity()) {
          field.classList.add("is-invalid");
          valid = false;
        }
      });

      if (!valid) {
        var error = getStepError(step);
        if (error) error.classList.add("is-visible");
      }
      return valid;
    }

    function getFieldValue(name) {
      var el = form.elements[name];
      if (!el) return "";
      if (el.type === "radio") {
        var checked = form.querySelector('input[name="' + name + '"]:checked');
        return checked ? checked.value : "";
      }
      return el.value || "";
    }

    function generateReference() {
      var now = new Date();
      var y = String(now.getFullYear()).slice(-2);
      var m = String(now.getMonth() + 1).padStart(2, "0");
      var d = String(now.getDate()).padStart(2, "0");
      var rand = Math.random().toString(16).slice(2, 6).toUpperCase();
      return "LMN-" + y + m + d + "-" + rand;
    }

    function buildSuccessSummary(ref) {
      var rows = [
        {
          label: "Vehicle Type",
          value: vehicleTypeLabels[getFieldValue("vehicle_type")] || getFieldValue("vehicle_type")
        },
        { label: "Model", value: getFieldValue("model") },
        { label: "Year", value: getFieldValue("vehicle_year") }
      ];
      successSummary.innerHTML = "";
      rows.forEach(function (row) {
        var el = document.createElement("div");
        el.className = "yl-qualifier__summary-row";
        el.innerHTML =
          '<span class="yl-qualifier__summary-label">' +
          row.label +
          '</span><span class="yl-qualifier__summary-value">' +
          (row.value || "—") +
          "</span>";
        successSummary.appendChild(el);
      });
      referenceText.textContent = "Reference #" + ref;
    }

    function setStatus(message) {
      if (!statusEl) return;
      statusEl.textContent = message || "";
    }

    function showSuccess(ref, serverReference) {
      var finalRef = serverReference || ref;
      form.classList.remove("is-submitting");
      form.classList.add("is-submitted");
      form.dataset.ylQualifierSubmitting = "0";
      submitBtn.disabled = false;
      setStatus("");
      formTitle.innerHTML = "Your case review is<br>on its way to us";
      formSubtitle.textContent =
        "A member of our team reads every submission personally and will follow up within one business day.";
      buildSuccessSummary(finalRef);
      referenceText.textContent = "Reference #" + finalRef;
      if (successPanel) successPanel.classList.add("is-visible");
    }

    function resetForm() {
      form.reset();
      form.classList.remove("is-submitted", "is-submitting");
      form.dataset.ylQualifierSubmitting = "0";
      currentStep = 1;
      setStatus("");
      if (successPanel) successPanel.classList.remove("is-visible");
      formTitle.textContent = "See if your vehicle qualifies";
      formSubtitle.innerHTML =
        'Answer a few questions. Our team reviews every submission personally. <span class="yl-qualifier__subtitle-accent">All fields marked * are required.</span>';
      form.querySelectorAll(".is-selected").forEach(function (el) {
        el.classList.remove("is-selected");
      });
      form.querySelectorAll(".is-invalid").forEach(function (el) {
        el.classList.remove("is-invalid");
      });
      form.querySelectorAll(".yl-qualifier__error.is-visible").forEach(function (el) {
        el.classList.remove("is-visible");
      });
      submitBtn.disabled = false;
      updateUI();
    }

    function setButtonVisible(btn, visible) {
      if (!btn) return;
      btn.hidden = !visible;
      btn.classList.toggle("is-hidden", !visible);
      btn.setAttribute("aria-hidden", visible ? "false" : "true");
    }

    function updateUI() {
      steps.forEach(function (stepEl) {
        stepEl.classList.toggle(
          "is-active",
          Number(stepEl.getAttribute("data-step")) === currentStep
        );
      });

      stepLabelEl.textContent = "Step " + currentStep + " of " + TOTAL_STEPS;
      stepNameEl.textContent = stepLabels[currentStep];
      progressFill.style.width = (currentStep / TOTAL_STEPS) * 100 + "%";
      progressBar.setAttribute("aria-valuenow", String(currentStep));

      var isFirst = currentStep === 1;
      var isLast = currentStep === TOTAL_STEPS;

      backBtn.disabled = isFirst;
      backBtn.setAttribute("aria-disabled", isFirst ? "true" : "false");
      backBtn.classList.toggle("is-enabled", !isFirst);

      setButtonVisible(nextBtn, !isLast);
      setButtonVisible(submitBtn, isLast);
      nextBtn.classList.toggle("is-accent", currentStep > 1);
    }

    function showSubmitError(message) {
      submitBtn.disabled = false;
      form.classList.remove("is-submitting");
      form.dataset.ylQualifierSubmitting = "0";
      setStatus("");
      var error = getStepError(4);
      if (error) {
        error.textContent = message || "Submission failed.";
        error.classList.add("is-visible");
      }
    }

    function buildAjaxUrl(baseUrl) {
      var url = baseUrl || "";
      if (!url) return "";
      if (url.indexOf("action=") !== -1) return url;
      return url + (url.indexOf("?") === -1 ? "?" : "&") + "action=yl_qualifier_submit";
    }

    function formDataToObject(formData) {
      var payload = {};
      if (formData && typeof formData.forEach === "function") {
        formData.forEach(function (value, key) {
          payload[key] = value;
        });
      } else if (formData && typeof formData.entries === "function") {
        var iterator = formData.entries();
        var item = iterator.next();
        while (!item.done) {
          payload[item.value[0]] = item.value[1];
          item = iterator.next();
        }
      }
      return payload;
    }

    function formDataSet(formData, key, value) {
      if (typeof formData.set === "function") {
        formData.set(key, value);
      } else {
        formData.append(key, value);
      }
    }

    var SUBMIT_TIMEOUT_MS = 45000;

    function fetchWithTimeout(url, options, timeoutMs) {
      timeoutMs = timeoutMs || SUBMIT_TIMEOUT_MS;
      if (typeof AbortController === "undefined") {
        return fetch(url, options);
      }

      var controller = new AbortController();
      var timer = setTimeout(function () {
        controller.abort();
      }, timeoutMs);

      var requestOptions = options || {};
      requestOptions.signal = controller.signal;

      return fetch(url, requestOptions).finally(function () {
        clearTimeout(timer);
      });
    }

    function parseResponseJson(text) {
      if (!text) return null;
      try {
        return JSON.parse(text);
      } catch (error) {
        var start = text.indexOf("{");
        var end = text.lastIndexOf("}");
        if (start !== -1 && end > start) {
          try {
            return JSON.parse(text.slice(start, end + 1));
          } catch (innerError) {
            return null;
          }
        }
      }
      return null;
    }

    function requestSubmitJson(url, payload) {
      var headers = {
        "Content-Type": "application/json",
        Accept: "application/json"
      };
      if (wpConfig.restNonce) {
        headers["X-WP-Nonce"] = wpConfig.restNonce;
      }

      return fetchWithTimeout(url, {
        method: "POST",
        headers: headers,
        body: JSON.stringify(payload),
        credentials: "same-origin"
      }).then(function (response) {
        return response.text().then(function (text) {
          var data = parseResponseJson(text);
          return { ok: response.ok, status: response.status, data: data, raw: text, url: url };
        });
      });
    }

    function requestSubmit(url, formData) {
      return fetchWithTimeout(url, {
        method: "POST",
        headers: {
          Accept: "application/json"
        },
        body: formData,
        credentials: "same-origin"
      }).then(function (response) {
        return response.text().then(function (text) {
          var data = parseResponseJson(text);
          return { ok: response.ok, status: response.status, data: data, raw: text, url: url };
        });
      });
    }

    function shouldTryFallback(result) {
      if (!result) return true;
      if (result.data && result.data.success) return false;
      return (
        !result.data ||
        !result.ok ||
        result.status === 400 ||
        result.status === 403 ||
        result.status === 404 ||
        result.status >= 500 ||
        result.raw === "0" ||
        result.raw === "-1"
      );
    }

    function handleSubmitResult(ref, result) {
      if (result.data && result.data.success) {
        var serverRef =
          (result.data.data && result.data.data.reference) ||
          (result.data.data && result.data.data.reference_number) ||
          ref;
        showSuccess(ref, serverRef);
        return;
      }

      var message = "Could not send your submission. Please try again.";
      var dataMessage = "";
      if (result.data && result.data.data && typeof result.data.data.message === "string") {
        dataMessage = result.data.data.message;
      } else if (result.data && typeof result.data.data === "string") {
        dataMessage = result.data.data;
      } else if (result.data && typeof result.data.message === "string") {
        dataMessage = result.data.message;
      }

      if (dataMessage) {
        message = dataMessage;
      } else if (result.status === 400 || result.status === 403) {
        message =
          "Server rejected the request (" +
          result.status +
          "). If Wordfence is active, allowlist the form endpoints or put it in Learning Mode, then try again.";
      } else if (result.raw === "0" || result.raw === "-1") {
        message = "Form handler is not available. Deactivate the old YL Qualifier plugin if it is still active.";
      } else if (!result.data && result.raw) {
        if (/critical error/i.test(result.raw)) {
          message =
            "WordPress returned a critical error on submit. The form handler must be loaded in functions.php.";
        } else {
          message =
            "Server returned an invalid response. If WP Mail SMTP debug is on, turn it off and try again.";
        }
      }

      if (window.console && window.console.error) {
        window.console.error("YL Qualifier submit failed", result);
      }

      showSubmitError(message);
    }

    function sendToWordPress(ref, formData) {
      var ajaxUrl = buildAjaxUrl(wpConfig.ajaxUrl);
      var restUrl = wpConfig.restUrl || "";
      var postUrl = wpConfig.postUrl || "";
      var frontUrl = wpConfig.frontUrl || "";
      var nonceInput = form.querySelector('input[name="nonce"]');
      var nonce =
        wpConfig.nonce ||
        (nonceInput && nonceInput.value) ||
        (typeof formData.get === "function" ? formData.get("nonce") : "") ||
        "";

      formDataSet(formData, "action", "yl_qualifier_submit");
      formDataSet(formData, "nonce", nonce);

      var payload = formDataToObject(formData);
      payload.action = "yl_qualifier_submit";
      payload.nonce = nonce;

      var attempts = [];

      // Prefer non-/wp-admin/ endpoints first — Wordfence often blocks admin-ajax/admin-post.
      if (restUrl) {
        attempts.push(function () {
          return requestSubmitJson(restUrl, payload);
        });
      }

      if (frontUrl) {
        attempts.push(function () {
          formDataSet(formData, "action", "yl_qualifier_submit");
          return requestSubmit(frontUrl, formData);
        });
      }

      if (ajaxUrl) {
        attempts.push(function () {
          return requestSubmit(ajaxUrl, formData);
        });
      }

      if (postUrl) {
        attempts.push(function () {
          formDataSet(formData, "action", "yl_qualifier_submit");
          return requestSubmit(postUrl, formData);
        });
      }

      function runAttempt(index) {
        if (index >= attempts.length) {
          showSubmitError("Could not send your submission. Please try again or call us directly.");
          return Promise.resolve();
        }

        return attempts[index]().then(function (result) {
          if (result.data && result.data.success) {
            handleSubmitResult(ref, result);
            return;
          }

          if (shouldTryFallback(result) && index + 1 < attempts.length) {
            return runAttempt(index + 1);
          }

          handleSubmitResult(ref, result);
        });
      }

      return runAttempt(0).catch(function (error) {
        if (error && error.name === "AbortError") {
          showSubmitError("The server took too long to respond. Your submission may still have been saved — please wait before trying again.");
          return;
        }

        if (attempts.length > 1) {
          return runAttempt(1);
        }

        showSubmitError("Network error. Please try again or call us directly.");
      });
    }

    backBtn.addEventListener("click", function () {
      if (currentStep <= 1) return;
      clearStepErrors(currentStep);
      currentStep -= 1;
      updateUI();
    });

    nextBtn.addEventListener("click", function () {
      if (!validateStep(currentStep)) return;
      if (currentStep >= TOTAL_STEPS) return;
      currentStep += 1;
      updateUI();
    });

    if (copyBtn) {
      copyBtn.addEventListener("click", function () {
        var text = referenceText.textContent.replace("Reference #", "");
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text);
        }
      });
    }

    if (restartBtn) restartBtn.addEventListener("click", resetForm);

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      if (form.dataset.ylQualifierSubmitting === "1") return;
      if (!validateStep(currentStep)) return;

      var ref = generateReference();
      var formData = new FormData(form);
      formDataSet(formData, "reference_number", ref);

      form.dataset.ylQualifierSubmitting = "1";
      form.classList.add("is-submitting");
      submitBtn.disabled = true;
      setStatus("Sending your submission…");

      if (
        !wpConfig.restUrl &&
        !wpConfig.frontUrl &&
        !wpConfig.postUrl &&
        !buildAjaxUrl(wpConfig.ajaxUrl)
      ) {
        form.dataset.ylQualifierSubmitting = "0";
        showSubmitError("Form is not configured. Contact the site administrator.");
        return;
      }

      sendToWordPress(ref, formData).finally(function () {
        if (!form.classList.contains("is-submitted")) {
          form.dataset.ylQualifierSubmitting = "0";
          form.classList.remove("is-submitting");
          submitBtn.disabled = false;
        }
      });
    });

    form.querySelectorAll("input, textarea").forEach(function (field) {
      field.addEventListener("input", function () {
        field.classList.remove("is-invalid");
      });
      field.addEventListener("change", function () {
        field.classList.remove("is-invalid");
      });
    });

    updateUI();
  }

  ready(function () {
    document.querySelectorAll("#yl-qualifier-form, form.yl-qualifier").forEach(initForm);
  });
})();
