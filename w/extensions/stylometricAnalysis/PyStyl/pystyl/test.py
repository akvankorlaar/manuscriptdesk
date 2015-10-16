"""
Ideas: The output directory should be a temporary directory, refreshed or deleted every few hours. If the user saves the result, it should be moved to a temporary place
"""

import sys
import os
import json

#from pystyl.corpus import Corpus
#from pystyl.analysis import pca, tsne, distance_matrix, hierarchical_clustering, vnc_clustering, bootstrapped_distance_matrices, bootstrap_consensus_tree
#from pystyl.visualization import scatterplot, scatterplot_3d, clustermap, scipy_dendrogram, ete_dendrogram, bct_dendrogram

#print 'hello world'

argument = os.path.expanduser(sys.argv[1])
print argument

json_object = json.loads(argument)
print json_object



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