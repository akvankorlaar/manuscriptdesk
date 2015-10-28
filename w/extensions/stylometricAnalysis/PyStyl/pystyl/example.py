"""
Ideas: The output directory should be a temporary directory, refreshed or deleted every few hours. If the user saves the result, it should be moved to a temporary place
"""


import sys
import os
import ast

from pystyl.corpus import Corpus
from pystyl.analysis import pca, tsne, distance_matrix, hierarchical_clustering, vnc_clustering, bootstrapped_distance_matrices, bootstrap_consensus_tree
from pystyl.visualization import scatterplot, scatterplot_3d, clustermap, scipy_dendrogram, ete_dendrogram, bct_dendrogram

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
    texts_information_dict = data['texts']
except:
    print "Data import error " + data
    sys.exit(1)

#texts_information_array = {'collection1' : {'title': 'title1', 'target_name' : 'target_name1', 'text':'Hello World, I was just typing this text, in order to get a very long text, because somehow the copy paste button does not work. Notwithstanding, the murmer of the fish was so loud that they jumped on the carrousel and sang "I love you John Myaer", who also happened to be there somehow, but that is part of another story'},
#                           'collection2' : {'title': 'title2', 'target_name': 'target_name2', 'text':'The crab fish is a very dangerous fish because the fish is a very large fish. Very large means that you can pretty much need a few kilometers of rape. About the length of Saturn or Earth which is pretty large. The mamoth of the fish is named "scorpius". Scorpius ruled the land of the fish for ions. He was a very smart fish also, and a friend of John Mayer'}}

if not os.path.isdir('../output/'):
    os.mkdir('../output/')

corpus = Corpus(language='en')

corpus.add_texts_manuscriptdesk(texts_information_dict = texts_information_dict)

corpus.preprocess(alpha_only=True, lowercase=True)


#additional tokenize options: self.corpus.tokenize(min_size=min_size,max_size=max_size, tokenizer_option=tokenizer_option)
corpus.tokenize()

corpus.segment(segment_size=0)

corpus.remove_tokens(rm_tokens=None, rm_pronouns=False, language='en') # watch out: if you do this before segment(), if will influence segment legths... (I would do it afterwards...)
#corpus.temporal_sort() # we assume that the categpries are sortable integers, indicating some order (e.g. date of composition)

#print(corpus)
corpus.vectorize(mfi=50, ngram_type='word', ngram_size=1, vector_space='tf_std')

#print(corpus.vectorizer.feature_names)

dms = bootstrapped_distance_matrices(corpus, n_iter=100, random_prop=0.20, metric='manhattan')

trees = [hierarchical_clustering(dm, linkage='ward') for dm in dms]

try:
    bct = bootstrap_consensus_tree(corpus=corpus, trees=trees, consensus_level=0.5)
except:
    print sys.exc_info()[0]
    sys.exit(1)

#plot
bct_dendrogram(corpus=corpus, tree=bct, fontsize=8, color_leafs=False,
                 mode='c', outputfile='../output/test.jpg', save=True)



                 #mode='c', outputfile='~/Desktop/bct_dendrogram.pdf', save=True)


# pca_coor, pca_loadings = pca(corpus, nb_dimensions=2)
# scatterplot(corpus, coor=pca_coor, nb_clusters=0, loadings=pca_loadings, plot_type='static')
# scatterplot(corpus, coor=pca_coor, nb_clusters=0, plot_type='interactive')
# pca_matrix_3d, _ = pca(corpus, nb_dimensions=3)
# scatterplot_3d(corpus, coor=pca_matrix_3d, nb_clusters=4)
#
# tsne_coor = tsne(corpus, nb_dimensions=2)
# scatterplot(corpus, coor=tsne_coor, nb_clusters=0, plot_type='static')
# scatterplot(corpus, coor=tsne_coor, nb_clusters=0, plot_type='interactive')
# tsne_coor_3d = tsne(corpus, nb_dimensions=3)
# scatterplot_3d(corpus, coor=tsne_coor_3d, nb_clusters=4)

#dm = distance_matrix(corpus, metric='minmax')
#clustermap(corpus, distance_matrix=dm, fontsize=8, color_leafs=True,
#           show=False, outputfile='~/Desktop/clustermap.pdf', save=True)


# cluster_tree = hierarchical_clustering(dm, linkage='ward')
# scipy_dendrogram(corpus=corpus, tree=cluster_tree, fontsize=8, color_leafs=False,
#                  show=False, outputfile='~/Desktop/scipy_dendrogram.pdf', save=True)
# ete_dendrogram(corpus=corpus, tree=cluster_tree, fontsize=8, color_leafs=False,
#                  mode='c', outputfile='~/Desktop/ete_dendrogram.pdf', save=True)
#
# vnc_tree = vnc_clustering(dm, linkage='ward')
# scipy_dendrogram(corpus, tree=vnc_tree, fontsize=8, color_leafs=False)
# ete_dendrogram(corpus, tree=vnc_tree, fontsize=8, color_leafs=False, mode='r')



