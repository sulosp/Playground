class Cat:
    def __init__(self, name, age):
        self.name = name
        self.age = age

    def sound(self, sound):
        return self.name + " says " + sound


my_cat = Cat('Whiskers', 3)
print(my_cat.sound('meow'))
