
def printreverse(arr, n):
    if n == 0:
        print()
    else:
        print(arr[n - 1], end='')
        printreverse(arr, n - 1)

printreverse([1, 2, 3, 4, 5], 5)