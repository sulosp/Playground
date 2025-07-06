print ('Bank of Ceylon')

pin = int(input('Enter your pin : '))

while pin!= 1234:
    pin =int(input ('Incorrect pin. Enter your pin again : '))


if pin == 1234:
    print ('Pin accepted.')