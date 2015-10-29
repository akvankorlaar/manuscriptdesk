"""
Ideas: The output directory should be a temporary directory, refreshed or deleted every few hours. If the user saves the result, it should be moved to a temporary place
"""

from phpserialize import *
import sys
import os
import json
import pprint
import ast

#from pystyl.corpus import Corpus
#from pystyl.analysis import pca, tsne, distance_matrix, hierarchical_clustering, vnc_clustering, bootstrapped_distance_matrices, bootstrap_consensus_tree
#from pystyl.visualization import scatterplot, scatterplot_3d, clustermap, scipy_dendrogram, ete_dendrogram, bct_dendrogram

#texts_information_dict = {'collection1' : {'title': 'title1', 'target_name' : 'target_name1', 'text':'Hello World, I was just typing this text, in order to get a very long text, because somehow the copy paste button does not work. Notwithstanding, the murmer of the fish was so loud that they jumped on the carrousel and sang "I love you John Myaer", who also happened to be there somehow, but that is part of another story'},
#                           'collection2' : {'ti                                                tle': 'title2', 'target_name': 'target_name2', 'text':'The crab fish2 is a very dangerous fish because the fish is a very large fish. Very large means that you can pretty much need a few kilometers of rope. About the length of Saturn or Earth which is pretty large. The mamoth of the fish is named "scorpius". Scorpius ruled the land of the fish for ions. He was a very smart fish also, and a friend of John Mayer'}}




# Load the data that PHP sent us
try:
    data = ast.literal_eval(sys.argv[1])
    removenonalpha = data['removenonalpha']
    lowercase = data['lowercase']
    tokenizer = data['tokenizer']
    minimumsize = data['minimumsize']
    maximumsize = data['maximumsize']
    segmentsize = data['segmentsize']
    stepsize = data['stepsize']
    removepronouns = data['removepronouns']
    vectorspace = data['vectorspace']
    featuretype = data['featuretype']
    ngramsize = data['ngramsize']
    mfi = data['mfi']
    minimumdf = data['minimumdf']
    maximumdf = data['maximumdf']
    texts_information_array = data['texts']

except:
    print "ERROR " + data
    sys.exit(1)

for i in texts_information_array:
    print texts_information_array[i]['title']







# Send it to stdout (to PHP)



# def getArgs(number):
#   try:
#     argument = os.path.expanduser(sys.argv[1])
#   except IndexError, e:
#     return sys.exit('noargs')
#   return argument
#
# def decodeJson(input):
#   try:
#     json_object = json.loads(input)
#   except ValueError, e:
#     return sys.exit('notjson')
#   return json_object
#
# argument = getArgs(1)
# loaded_json = decodeJson(argument)
#
#
# print loaded_json