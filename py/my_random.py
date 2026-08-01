import numpy as np

rng= np.random.default_rng()

a= rng.integers(2, 10, 2 )
b= rng.integers(2, 10, 2 )

c = np.sqrt(a**2+b**2)

print(f'{a},{b},{c}')

print(f'{rng.random(5)}')