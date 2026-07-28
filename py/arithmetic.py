import numpy as np

e= np.log(np.e) #print 1
print(e)

d = 2e3
print(d)
print('\n')

a = float(input("a = "))
b = float(input("b = "))

c = np.sqrt(a**2 + b**2)
A = 1/2*(a*b)

print('hypotenuse length = ', c )
print(f'hypotenuse length = {c:.2f}')  # rounds to 2 decimal places
print(f'Area of the triangle = {a}')

print(f'\n')
x= float(input("x = "))
x_rad = np.radians(x)
print(f'{np.cos(x_rad):.5f}')

