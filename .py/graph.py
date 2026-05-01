import numpy as np
import matplotlib.pyplot as plt

# File path
gdp_data_csv = r"C:\Users\uni\Downloads\API_NY.GDP.PCAP.CD_DS2_en_csv_preprocessed.csv"

# Load raw CSV
raw = np.genfromtxt(gdp_data_csv, delimiter=',', dtype=str, invalid_raise=False)

# Extract country rows (example row numbers)
germany = raw[56, 4:]
usa = raw[1, 4:]
samoa = raw[200, 4:]

# Clean and convert to float
def clean(arr):
    arr = np.array(arr)
    arr = arr[arr != '']
    return arr.astype(float)

germany = clean(germany)
usa = clean(usa)
samoa = clean(samoa)

# Ensure years match data length
years = np.arange(1960, 1960 + len(germany))

plt.figure(figsize=(10, 6))
plt.plot(years, germany, color='tab:blue', linestyle='-', linewidth=2, label='Germany')
plt.plot(years, usa[:len(years)], color='tab:green', linestyle='--', linewidth=2, label='USA')
plt.plot(years, samoa[:len(years)], color='tab:red', linestyle=':', linewidth=2, label='Samoa')

plt.xlabel('Year')
plt.ylabel('GDP per capita (current US$)')
plt.title('GDP per capita (1960–2024): Germany vs USA vs Samoa')
plt.legend()
plt.grid(True)
plt.tight_layout()
plt.show()
