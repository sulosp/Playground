import numpy as np

sentence = 'The quick brown fox jumps over the lazy dog'

print(sentence[:5])
print(sentence[-5:])

print(sentence[2:15:2])

vowels=['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U']

sentence_new= ''.join([letter for letter in sentence if letter not in vowels])
print(sentence_new)