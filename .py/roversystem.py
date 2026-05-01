import numpy as np

# Define dtypes
dtype32 = np.dtype([
    ('timestamp', np.uint32),
    ('pressure', np.float32),
    ('temperature', np.float32),
    ('status', np.bool_)
])

dtype64 = np.dtype([
    ('timestamp', np.uint32),
    ('pressure', np.float64),
    ('temperature', np.float64),
    ('status', np.bool_)
])

# Entry sizes
entry_size_32 = dtype32.itemsize
entry_size_64 = dtype64.itemsize

# 1 MB in bytes
one_mb = 1 * 1024 * 1024

# Number of entries
entries_32 = one_mb // entry_size_32
entries_64 = one_mb // entry_size_64

print("Entry size (float32):", entry_size_32, "bytes")
print("Entry size (float64):", entry_size_64, "bytes")
print("Entries storable in 1MB with float32:", entries_32)
print("Entries storable in 1MB with float64:", entries_64)
