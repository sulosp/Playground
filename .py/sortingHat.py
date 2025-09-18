# Write code below 💖
G =0
R = 0
H = 0
S = 0

print ('Do you like Dawn or Dusk?')
print (' 1.) Dawn and 2.) Dusk')
q1 = int(input('Enter your answer : '))

if q1 == 1:
  G = G + 1
  R = R + 1
elif q1 ==2 :
  H = H +1
  S = S + 1
else :
  print('Wrong Input')


print ('When I\'m dead, I want people to remember me as:')
print ('1.) The Good')
print ('2.) The Great')
print ('3.) The Wise')
print ('4.) The Bold')

q2 = int(input('Enter your answer : '))

if q2 ==1:
  H = H+2
elif q2 == 2:
  S = S+2
elif q2 == 3:
  R = R +2
elif q2 == 4:
  G = G +2
else :
  print('Wrong input')


print('Which kind of instrument most pleases your ear?')
print ('1.) The Violin')
print ('2.) The Trumpet')
print ('3.) The Piano ')
print ('4.) The Drum')

q3 = int(input('Enter your answer : '))

if q3 == 1:
  S = S + 4
elif q3 == 2:
  H = H + 4
elif q3 == 3:
  R = R + 4
elif q3 == 4 :
  G = G + 4
else :
  print ('Wrong Input')

if R > S and R > H and R > G:
  print (' Ravenclaw')
elif S > R and S > H and S > G:
  print ('Slytherin')
elif H > R and H > S and H > G:
  print ('Hufflepuff')
elif G > R and G > S and G > H:
  print ('Gryffindor')