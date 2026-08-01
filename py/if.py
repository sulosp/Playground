def is_leap(year):
    if (year%4==0):
        if (year%100==0):
            if (year%400==0):
                return True
            else:
                return False  # explicitly return False here
        else:
            return True  # divisible by 4 but not by 100
    else:
        return False  # not divisible by 4


print(is_leap(2000))  # True
print(is_leap(1900))  # False
print(is_leap(2024))  # True
print(is_leap(2023))  # False