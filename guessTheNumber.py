guess = 0
tries = 0

while guess != 6 and tries < 5:
    guess = int(input('Enter the number : '))
    tries = tries + 1

if guess != 6:
    print('Your tries are over')
else:
    print('You got this!')