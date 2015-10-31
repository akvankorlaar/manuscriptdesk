import sys
import os
import ast

from pystyl.corpus import Corpus
from pystyl.analysis import pca, tsne, distance_matrix, hierarchical_clustering, vnc_clustering, bootstrapped_distance_matrices, bootstrap_consensus_tree
from pystyl.visualization import scatterplot, scatterplot_3d, clustermap, scipy_dendrogram, ete_dendrogram, bct_dendrogram

web_run = 0

if web_run:

    #import the data
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
        featuretype = data['featuretype'] #not available yet
        ngramsize = data['ngramsize']
        mfi = data['mfi']
        minimumdf = data['minimumdf']
        maximumdf = data['maximumdf']

        base_outputpath = data['base_outputpath']
        full_outputpath = data['full_outputpath']

        visualization1 = data['visualization1']
        visualization2 = data['visualization2']

        texts_information_dict = data['texts']
    except:
        print "importerror"
        sys.exit(1)

#for testing purposes
else:

    texts_information_dict = {'collection1' : {'title': 'title1', 'target_name' : 'target_name1', 'text':'Hello World, I was just typing this text, in order to get a very long text, because somehow the copy paste button does not work. Notwithstanding, the murmer of the fish was so loud that they jumped on the carrousel and sang "I love you John Myaer", who also happened to be there somehow, but that is part of another story'},
                           'collection2' : {'title': 'title2', 'target_name': 'target_name2', 'text':'The crab fish2 is a very dangerous fish because the fish is a very large fish. Very large means that you can pretty much need a few kilometers of rope. About the length of Saturn or Earth which is pretty large. The mamoth of the fish is named "scorpius". Scorpius ruled the land of the fish for ions. He was a very smart fish also, and a friend of John Mayer'
                            }, 'collection3' : {'title': 'title3', 'target_name': 'target_name3', 'text':'The crab fish2 is a very dangerous fish because the fish is a very large fish. Very large means that you can pretty much need a few kilometers of rope. About the length of Saturn or Earth which is pretty large. The mamoth of the fish is named "scorpius". Scorpius ruled the land of the fish for ions. He was a very smart fish also, and a friend of John Mayer'
                            }}

    removenonalpha = 1
    lowercase = 1
    tokenizer = 'whitespace'
    minimumsize = 0
    maximumsize = 100
    segmentsize = 0
    stepsize = 0
    removepronouns = 1
    vectorspace = 'tf'
    featuretype = 0 #not available yet
    ngramsize = 50
    mfi = 20
    minimumdf = 0
    maximumdf = 0.9

    base_outputpath = 'C:/xampp/htdocs/mediawikinew/initialStylometricAnalysis/test'
    full_outputpath = 'C:/xampp/htdocs/mediawikinew/initialStylometricAnalysis/test/test.jpg'

#error if destination files already exist
if os.path.isfile(full_outputpath):
    print 'patherror'
    sys.exit(1)

#make the base directory if it does not already exist
if not os.path.exists(base_outputpath) and web_run:
    os.makedirs(base_outputpath)

#do the analysis and save the output
try:
    corpus = Corpus(language='en')
    corpus.add_texts_manuscriptdesk(texts_information_dict = texts_information_dict)
    #corpus.add_directory(directory='data/dummy')
    corpus.preprocess(alpha_only=removenonalpha, lowercase=lowercase)

    corpus.tokenize(min_size=minimumsize, max_size=maximumsize, tokenizer_option=tokenizer) #defaults can be used
    corpus.segment(segment_size=segmentsize, step_size = stepsize)#allow segmentatation? May cause problems..
    corpus.remove_tokens(rm_tokens=None, rm_pronouns=removepronouns, language='en') # watch out: if you do this before segment(), if will influence segment legths... (I would do it afterwards...)   Does removepronouns work!?

    corpus.vectorize(mfi=mfi, ngram_type='word', ngram_size=ngramsize, vector_space=vectorspace,min_df=minimumdf, max_df=maximumdf)

    if web_run:

        def constructAndSaveVisualization(visualization):

            """
            select the appropriate analysis methods
            save in the appropriate place with the appropriate name
            :param visualization1: the visualization selected by the user
            :return: true of succcess, otherwise return error
            """

            if visualization == 'dendrogram':
                dms = bootstrapped_distance_matrices(corpus, n_iter=100, random_prop=0.20, metric='manhattan')
                trees = [hierarchical_clustering(dm, linkage='ward') for dm in dms]
                bct = bootstrap_consensus_tree(corpus=corpus, trees=trees, consensus_level=0.5)
                bct_dendrogram(corpus=corpus, tree=bct, fontsize=8, color_leafs=False,mode='c', outputfile=full_outputpath, save=True)
            elif visualization == 'pcascatterplot':
                pca_coor, pca_loadings = pca(corpus)
                scatterplot(corpus, coor=pca_coor, loadings=pca_loadings, plot_type='static', outputfile=full_outputpath, save=True)
            elif visualization == 'tnsescatterplot':
                tsne_coor = tsne(corpus, nb_dimensions=2)
                scatterplot(corpus, coor=tsne_coor, nb_clusters=0, plot_type='static', outputfile=full_outputpath, save=True)
            elif visualization == 'distancematrix':
                dm = distance_matrix(corpus, metric='minmax')
                clustermap(corpus, distance_matrix=dm, fontsize=8, color_leafs=True, outputfile=full_outputpath, save=True)
            elif visualization == 'neighbourclustering':
                dm = distance_matrix(corpus, metric='minmax')
                vnc_tree = vnc_clustering(dm, linkage='ward')
                scipy_dendrogram(corpus, tree=vnc_tree, fontsize=8, color_leafs=False, outputfile=full_outputpath, save=True)
            return

        constructAndSaveVisualization(visualization1)
        constructAndSaveVisualization(visualization2)

    else:
        dms = bootstrapped_distance_matrices(corpus, n_iter=100, random_prop=0.20, metric='manhattan')
        trees = [hierarchical_clustering(dm, linkage='ward') for dm in dms]
        bct = bootstrap_consensus_tree(corpus=corpus, trees=trees, consensus_level=0.5)
        bct_dendrogram(corpus=corpus, tree=bct, fontsize=8, color_leafs=False,mode='c', show=True)

        # pca_matrix_3d, _ = pca(corpus, nb_dimensions=3)
        # scatterplot_3d(corpus, coor=pca_matrix_3d, outputfile='random', nb_clusters=4, show=True) #only works when number of texts = 3

        #tsne_coor = tsne(corpus, nb_dimensions=2)
        #scatterplot(corpus, coor=tsne_coor, nb_clusters=0, plot_type='static', show=True)#works

        #dm = distance_matrix(corpus, metric='minmax') #works
        #clustermap(corpus, distance_matrix=dm, fontsize=8, color_leafs=True,show=True)

        #vnc_tree = vnc_clustering(dm, linkage='ward')#works
        #scipy_dendrogram(corpus, tree=vnc_tree, fontsize=8, color_leafs=False)

        #visualization options:
        #dendogram
        #pca scatterplot
        #pca 3d scatterplot
        #tnse scatterplot
        #distance masterix clustering
        #hierarchical clustering
        #variability based neighbour clustering


except TypeError as E:
    exc_type, exc_obj, exc_tb = sys.exc_info()
    fname = os.path.split(exc_tb.tb_frame.f_code.co_filename)[1]
    print(exc_type, fname, exc_tb.tb_lineno)
    print E




