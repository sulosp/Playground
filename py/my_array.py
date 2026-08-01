girls_names = ["Emilia", "Sophia", "Emma", "Hannah", 
               "Mia", "Lina", "Ella", "Lia", "Leni", "Mila"]

# Top 3
print(' '.join(girls_names[:3]))

# Odd places (1,3,...)
print(' '.join(girls_names[::2]))

# Specific places (2,3,6,9,10)
print(' '.join([girls_names[i] for i in [1,2,5,8,9]]))