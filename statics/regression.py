import numpy as np

x = np.loadtxt("toydata_x.txt")
y = np.loadtxt("toydata_y.txt")

print(x)
print(y)

x0= x[ y == 0 ]
x1= x[ y == 1 ]

print("x0 : ", x0)
print("x1 : ", x1)

m0 = np.mean(x0)
m1 = np.mean(x1)

print("m0 : ", m0)
print("m1 : ", m1)