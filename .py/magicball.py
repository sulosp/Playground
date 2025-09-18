import random

Question = input('Enter your question : ')

num = random.randint(0,8)

if num == 1 :
    print('Yes - definitely')
elif num == 2 :
    print ( 'Absolutely, That\'s a great idea')
elif num ==3:
    print('No - it\'s not possible')
elif num ==4:
    print ('Not looking good enough')
elif num == 5:
    print ('You need more support')
elif num == 6:
    print('this is possible')
elif num == 7:
    print('You are so awesome')
else :
    print('You can proceed')