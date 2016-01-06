/**
 * Copyright (C) 2013 Richard Davis
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License Version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 * @package MediaWiki
 * @subpackage Extensions
 * @author Richard Davis <r.davis@ulcc.ac.uk>
 * @author Ben Parish <b.parish@ulcc.ac.uk>
 * @copyright 2013 Richard Davis
 * 
 * Sept 2015: Added 8 new tags, and made some small changes @Arent van Korlaar
 */

/*
 * This adds the JBTEIToolbar to the deprecated Mediawiki toolbar. For more information see
 * (http://www.mediawiki.org/wiki/Customizing_edit_toolbar#How_do_I_add_more_buttons_on_the_edit_page.3F)
 */

var maxMin = '<button id="minimise" type="button" style="position:relative; float:right; z-index:999; top: 45px;">Minimise</button>'
			+ '<button id="maximise" type="button" style="position:relative; float:right; z-index:999; top: 45px">Maximise</button>';


var $images_path = "extensions/JBTEIToolbar/images/";

var addExtraButtonsToClassicToolBar = function(){
    /*
     * The toolbar needs to be moved above the edit form so that
     * the viewer will float alongside the text area
     */

    $('#toolbar').insertBefore('#editform');

    $('#wpTextbox1').insertBefore('#zoomviewerframe');  


	$('#toolbar').empty();

	mw.toolbar.addButton({
		'id'        : 'mw-editbutton-linebreak',
		'imageFile' : $images_path + 'jb-button-linebreak.png',
		'speedTip'  : mw.msg( 'toolbar-label-line-break' ),
		'tagOpen'   : '<lb/>',
		'tagClose'  : '',
		'sampleText': ''
	});

	mw.toolbar.addButton({
		'id'        : 'mw-editbutton-pagebreak',
		'imageFile' : $images_path + 'jb-button-pagebreak.png',
		'speedTip'  : mw.msg( 'toolbar-label-page-break' ),
		'tagOpen'   : '<pb/>',
		'tagClose'  : '',
		'sampleText': ''
	});

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-heading',
    	'imageFile' : $images_path + 'jb-button-heading.png',
        'speedTip'  : mw.msg( 'toolbar-label-heading' ),
        'tagOpen'   : '<head>',
        'tagClose'  : '</head>',
        'sampleText': mw.msg( 'toolbar-peri-heading' )
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-paragraph',
    	'imageFile' : $images_path + 'jb-button-paragraph.png',
        'speedTip'  : mw.msg( 'toolbar-label-paragraph' ),
        'tagOpen'   : '<p>',
        'tagClose'  : '</p>',
        'sampleText': mw.msg( 'toolbar-peri-paragraph' )
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-add',
    	'imageFile' : $images_path + 'jb-button-add.png',
        'speedTip'  : mw.msg( 'toolbar-label-addition' ),
        'tagOpen'   : '<add>',
        'tagClose'  : '</add>',
        'sampleText': mw.msg( 'toolbar-peri-addition' )
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-deletion',
    	'imageFile' : $images_path + 'jb-button-deletion.png',
        'speedTip'  : mw.msg( 'toolbar-label-deletion' ),
        'tagOpen'   : '<del>',
        'tagClose'  : '</del>',
        'sampleText': mw.msg( 'toolbar-peri-deletion' )
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-unclear',
    	'imageFile' : $images_path + 'jb-button-unclear.png',
        'speedTip'  : mw.msg( 'toolbar-label-unclear' ),
        'tagOpen'   : '<unclear>',
        'tagClose'  : '</unclear>',
        'sampleText': mw.msg( 'toolbar-peri-unclear' )
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-gap',
    	'imageFile' : $images_path + 'jb-button-gap.png',
        'speedTip'  : mw.msg( 'toolbar-label-gap' ),
        'tagOpen'   : '<gap/>',
        'tagClose'  : '',
        'sampleText': ''
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-note',
    	'imageFile' : $images_path + 'jb-button-note.png',
        'speedTip'  : mw.msg( 'toolbar-label-note' ),
        'tagOpen'   : '<note>',
        'tagClose'  : '</note>',
        'sampleText': mw.msg( 'toolbar-peri-note' )
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-underline',
    	'imageFile' : $images_path + 'jb-button-underline.png',
        'speedTip'  : mw.msg( 'toolbar-label-underline' ),
        'tagOpen'   : '<hi rend="underline">',
        'tagClose'  : '</hi>',
        'sampleText': mw.msg( 'toolbar-peri-underline' )
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-superscript',
    	'imageFile' : $images_path + 'jb-button-superscript.png',
        'speedTip'  : mw.msg( 'toolbar-label-superscript' ),
        'tagOpen'   : '<hi rend="superscript">',
        'tagClose'  : '</hi>',
        'sampleText': mw.msg( 'toolbar-peri-superscript' )
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-sic',
    	'imageFile' : $images_path + 'jb-button-sic.png',
        'speedTip'  : mw.msg( 'toolbar-label-spelling' ),
        'tagOpen'   : '<sic>',
        'tagClose'  : '</sic>',
        'sampleText':  mw.msg( 'toolbar-peri-spelling' )
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-foreign',
    	'imageFile' : $images_path + 'jb-button-foreign.png',
        'speedTip'  : mw.msg( 'toolbar-label-foreign' ),
        'tagOpen'   : '<foreign>',
        'tagClose'  : '</foreign>',
        'sampleText': mw.msg( 'toolbar-peri-foreign' )
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-ampersand',
    	'imageFile' : $images_path + 'jb-button-ampersand.png',
        'speedTip'  : mw.msg( 'toolbar-label-ampersand' ),
        'tagOpen'   : '&amp;',
        'tagClose'  : '',
        'sampleText': ''
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-longdash',
    	'imageFile' : $images_path + 'jb-button-longdash.png',
        'speedTip'  : mw.msg( 'toolbar-label-long-dash' ),
        'tagOpen'   : '&#x2014;',
        'tagClose'  : '',
        'sampleText': ''
    });

    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-comment',
    	'imageFile' : $images_path + 'jb-button-comment.png',
        'speedTip'  : mw.msg( 'toolbar-label-comment' ),
        'tagOpen'   : '<!-- ',
        'tagClose'  : ' -->',
        'sampleText': 'user comment'
    });
    
    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-retrace',
    	'imageFile' : $images_path + 'jb-button-retrace.png',
        'speedTip'  : mw.msg( 'toolbar-label-retrace' ),
        'tagOpen'   : '<retrace>',
        'tagClose'  : '</retrace>',
        'sampleText': 'toolbar-peri-retrace'
    });
    
    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-date',
    	'imageFile' : $images_path + 'jb-button-date.png',
        'speedTip'  : mw.msg( 'toolbar-label-date' ),
        'tagOpen'   : '<date>',
        'tagClose'  : '</date>',
        'sampleText': 'toolbar-peri-date'
    });
    
    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-name',
    	'imageFile' : $images_path + 'jb-button-name.png',
        'speedTip'  : mw.msg( 'toolbar-label-name' ),
        'tagOpen'   : '<name>',
        'tagClose'  : '</name>',
        'sampleText': 'toolbar-peri-name'
    });
    
    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-num',
    	'imageFile' : $images_path + 'jb-button-num.png',
        'speedTip'  : mw.msg( 'toolbar-label-num' ),
        'tagOpen'   : '<num>',
        'tagClose'  : '</num>',
        'sampleText': 'toolbar-peri-num'
    });
    
    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-title',
    	'imageFile' : $images_path + 'jb-button-title.png',
        'speedTip'  : mw.msg( 'toolbar-label-title' ),
        'tagOpen'   : '<title>',
        'tagClose'  : '</title>',
        'sampleText': 'toolbar-peri-title'
    });
    
    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-metamark',
    	'imageFile' : $images_path + 'jb-button-metamark.png',
        'speedTip'  : mw.msg( 'toolbar-label-metamark' ),
        'tagOpen'   : '<metamark>',
        'tagClose'  : '</metamark>',
        'sampleText': 'toolbar-peri-metamark'
    });
    
    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-restore',
    	'imageFile' : $images_path + 'jb-button-restore.png',
        'speedTip'  : mw.msg( 'toolbar-label-restore' ),
        'tagOpen'   : '<restore>',
        'tagClose'  : '</restore>',
        'sampleText': 'toolbar-peri-restore'
    });
    
    mw.toolbar.addButton({
    	'id'        : 'mw-editbutton-supplied',
    	'imageFile' : $images_path + 'jb-button-supplied.png',
        'speedTip'  : mw.msg( 'toolbar-label-supplied' ),
        'tagOpen'   : '<supplied>',
        'tagClose'  : '</supplied>',
        'sampleText': 'toolbar-peri-supplied'
    });

}

/*
 * BP 2013
 * This is for the new WikiEditor Extension if has been enabled
 * The following lines remove the existing sections and buttons.
 * The WikiEditor is then populated with the JB TEI button.
 * NOTE: For this to work the WikiEditor must be included
 * before this JBTEIToolBar extension is included. For example:
 *
 * require_once( 'WikiEditor/WikiEditor.php' );
 * $wgDefaultUserOptions['usebetatoolbar'] = 1;
 *
 * require_once( 'JBTEIToolbar/JBTEIToolbar.php' );
 *
 *
 */

var addExtraButtons = function() {
    
      /*
       * The toolbar needs to be moved above the edit form so that
       * the viewer will float alongside the text area
       */         
        $('.wikiEditor-ui-top').insertBefore('#editform')
        $('.wikiEditor-ui').insertAfter('.wikiEditor-ui-top');
        $('#wpTextbox1').insertBefore('#zoomviewerframe');
        
	$( '#wpTextbox1' ).wikiEditor( 'removeFromToolbar', {
        'section': 'advanced'
	});

	$( '#wpTextbox1' ).wikiEditor( 'removeFromToolbar', {
        'section': 'help'
	});

	$( '#wpTextbox1' ).wikiEditor( 'removeFromToolbar', {
        'section': 'characters'
	});

	$( '#wpTextbox1' ).wikiEditor( 'removeFromToolbar', {
		'section': 'main',
        'group': 'insert'
	});


	$( '#wpTextbox1' ).wikiEditor( 'removeFromToolbar', {
		'section': 'main',
        'group': 'format',
        'tool':'italic'
	} );

	$( '#wpTextbox1' ).wikiEditor( 'removeFromToolbar', {
		'section': 'main',
        'group': 'format',
        'tool':'bold'
	} );

	var $fullPath = window.location.pathname;

	var $lastSlashIndex = $fullPath.lastIndexOf('/');

	var $mw_root_directory = $fullPath.substring(0, $lastSlashIndex + 1 );

	var $path = $mw_root_directory + $images_path;

	$( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
		'section': 'main',
        'group'  : 'format',
        'tools'  : {

	        	'line-break': {
	        		label :  mw.msg( 'toolbar-label-line-break' ),
	        		type  : 'button',
	        		icon  : $path + 'jb-button-linebreak.png',
	        		action: {
	        			type   : 'encapsulate',
	        			options: {
	        				pre:  '<lb/>',
	        				peri: '',
	        				post: '',
	        			}
	        		}
	        	},

	        	'pagebreak': {
	        		label : mw.msg( 'toolbar-label-page-break' ),
	        		type  : 'button',
	        		icon  : $path + 'jb-button-pagebreak.png',
	        		action: {
	        			type   : 'encapsulate',
	        			options: {
	        				pre:  '<pb/>',
	        				peri: '',
	        				post: '',
	        			}
	        		}
	        	},

	            'heading': {
	                label : mw.msg( 'toolbar-label-heading' ),
	                type  : 'button',
	                icon  : $path + 'jb-button-heading.png',
	                action: {
	                        type   : 'encapsulate',
	                        options: {
	                            pre:  '<head>',
	                            peri: mw.msg( 'toolbar-peri-heading' ),
	                            post: '</head>',
	                        }
	                }
	            },

	            'paragraph': {
	                label : mw.msg( 'toolbar-label-paragraph' ),
	                type  : 'button',
	                icon  : $path + 'jb-button-paragraph.png',
	                action: {
	                        type   : 'encapsulate',
	                        options: {
	                            pre:  '<p>',
	                            peri: mw.msg( 'toolbar-peri-paragraph' ),
	                            post: '</p>',
	                        }
	                }
	            },

			    'addition': {
			        label : mw.msg( 'toolbar-label-addition' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-add.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<add>',
			                    peri: mw.msg( 'toolbar-peri-addition' ),
			                    post: '</add>',
			                }
			        }
			    },

			    'deletion': {
			        label : mw.msg( 'toolbar-label-deletion' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-deletion.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<del>',
			                    peri: mw.msg( 'toolbar-peri-deletion' ),
			                    post: '</del>',
			                }
			        }
			    },

			    'unclear': {
			        label : mw.msg( 'toolbar-label-unclear' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-unclear.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<unclear>',
			                    peri: mw.msg( 'toolbar-peri-unclear' ),
			                    post: '</unclear>',
			                }
			        }
			    },

			    'gap': {
			        label : mw.msg( 'toolbar-label-gap' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-gap.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<gap/>',
			                    peri: '',
			                    post: '',
			                }
			        }
			    },

			    'note': {
			        label : mw.msg( 'toolbar-label-note' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-note.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<note>',
			                    peri: mw.msg( 'toolbar-peri-note' ),
			                    post: '</note>',
			                }
			        }
			    },

			    'underline': {
			        label : mw.msg( 'toolbar-label-underline' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-underline.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<hi rend="underline">',
			                    peri: mw.msg( 'toolbar-peri-underline' ),
			                    post: '</hi>'
			                }
			        }
			    },

			    'superscript': {
			        label : mw.msg( 'toolbar-label-superscript' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-superscript.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                	pre : '<hi rend="superscript">',
			                    peri: mw.msg( 'toolbar-peri-superscript' ),
			                    post: '</hi>'
			                }
			        }
			    },

			    'sic': {
			        label : mw.msg( 'toolbar-label-spelling' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-sic.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre :  '<sic>',
			                    peri: mw.msg(  'toolbar-peri-spelling' ),
			                    post: '</sic>',
			                }
			        }
			    },

			    'foreign': {
			        label : mw.msg( 'toolbar-label-foreign' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-foreign.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre :  '<foreign>',
			                    peri: mw.msg(  'toolbar-peri-foreign' ),
			                    post: '</foreign>',
			                }
			        }
			    },

			    'ampersand': {
			        label : mw.msg( 'toolbar-label-ampersand' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-ampersand.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre :  '&amp;',
			                    peri: '',
			                    post: '',
			                }
			        }
			    },

			    'longdash': {
			        label : mw.msg( 'toolbar-label-long-dash' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-longdash.png',
			        action: {
			                type: 'encapsulate',
			                options: {
			                    pre : '&#x2014;',
			                    peri: '',
			                    post: '',
			                }
			        }
			    },

			    'commment': {
			        label : mw.msg( 'toolbar-label-comment' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-comment.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<!-- ',
			                    peri: mw.msg('toolbar-peri-comment'),
			                    post: ' -->',
			                }
			        }
			    },
                            
                           'retrace': {
			        label : mw.msg( 'toolbar-label-retrace' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-retrace.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<retrace>',
			                    peri: mw.msg('toolbar-peri-retrace'),
			                    post: '</retrace>',
			                }
			        }
			    },
                            
                           'date': {
			        label : mw.msg( 'toolbar-label-date' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-date.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<date>',
			                    peri: mw.msg('toolbar-peri-date'),
			                    post: '</date>',
			                }
			        }
			    },
                            
                           'name': {
			        label : mw.msg( 'toolbar-label-name' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-name.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<name>',
			                    peri: mw.msg('toolbar-peri-name'),
			                    post: '</name>',
			                }
			        }
			    },
                            
                           'num': {
			        label : mw.msg( 'toolbar-label-num' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-num.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<num>',
			                    peri: mw.msg('toolbar-peri-num'),
			                    post: '</num>',
			                }
			        }
			    },
                            
                            'title': {
			        label : mw.msg( 'toolbar-label-title' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-title.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<title>',
			                    peri: mw.msg('toolbar-peri-title'),
			                    post: '</title>',
			                }
			        }
			    },
                            
                            'metamark': {
			        label : mw.msg( 'toolbar-label-metamark' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-metamark.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<metamark>',
			                    peri: mw.msg('toolbar-peri-metamark'),
			                    post: '</metamark>',
			                }
			        }
			    },
                            
                            'restore': {
			        label : mw.msg( 'toolbar-label-restore' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-restore.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<restore>',
			                    peri: mw.msg('toolbar-peri-restore'),
			                    post: '</restore>',
			                }
			        }
			    },
                            
                            'supplied': {
			        label : mw.msg( 'toolbar-label-supplied' ),
			        type  : 'button',
			        icon  : $path + 'jb-button-supplied.png',
			        action: {
			                type   : 'encapsulate',
			                options: {
			                    pre:  '<supplied>',
			                    peri: mw.msg('toolbar-peri-supplied'),
			                    post: '</supplied>',
			                }
			        }
			    }
                            
                            
        }
	} );    
};


/* Check if view is in edit mode and that the required modules are available. Then, customize the toolbar . . . */

$(document).ready(function(){
    if( $.inArray( mw.config.get( 'wgAction' ), [ 'edit', 'submit' ] ) !== -1 ) {

        mw.loader.using( 'user.options', function () {
            if ( mw.user.options.get( 'showtoolbar' ) ) {

                if ( mw.user.options.get( 'usebetatoolbar' )) {
                    mw.loader.using( 'ext.wikiEditor.toolbar', function(){
                        $( addExtraButtons );
                    } );
                } else {
                    mw.loader.using( 'mediawiki.action.edit', function(){
                        $( addExtraButtonsToClassicToolBar );
                    } );
                }
                
                $('.firstHeading').append( maxMin );
                        
                /**
                 * This function restuctures the maximise and minimise css when clicking the maximise button
                 */
                $( '#maximise' ).click(function() {
                  
                    $("#mw-content-text").addClass("maximise");
                    $("html, body").animate({ scrollTop: 0 }, "fast");
                    $("#minimise").css("position", "absolute");
                    $("#minimise").css("right", "0px");
                    $("#minimise").css("top", "0px");                    
                    $("#maximise").css("position", "absolute");''
                    $("#maximise").css("right", "60px");
                    $("#maximise").css("top", "0px");                    
                });
                
                /**
                 * This function restructures the maximise and minimise css when clicking the minimise button
                 */
                $('#minimise').click(function() {
                  
                    $("#mw-content-text").removeClass("maximise");
                    $("#minimise").css("position", "relative")
                    $("#minimise").css("top", "45px");              
                    $("#maximise").css("position", "relative");
                    $("#maximise").css("right", "auto");
                    $("#maximise").css("top", "45px");              
                });
                
                /**
                 * This function performs javascript tag matching when submitting the form. <tag> and </tag> are matched. <tag/> is not matched. 
                 * 
                 * /<[a-zA-Z\d" =]+>/g
                 * 
                 * / and / are the regex delimiters. 
                 * 
                 * < and > means match the tags
                 * 
                 * [] are match group delimiters
                 * 
                 * [a-zA-Z\d"=]+ means match any charachter that is alphabetic lowercase, alphabetic upcercase, digit, " or =
                 * 
                 * + means, match the pattern in between [] once or more
                 * 
                 * g means to a global match (as opposed to matching it only once)
                 */
                $('#editform').submit(function(event) {
                                                      
                  var wp_textbox1 = $('#wpTextbox1').val();
                  
                  number_opened_tags = 0;
                  number_closed_tags = 0;
                                                      
                  var open_regex = /<[a-zA-Z\d" =#]+>/g;
                  var close_regex = /<\/[a-zA-Z\d]+>/g;
                               
                  var opened_tags = wp_textbox1.match(open_regex);
                  var closed_tags = wp_textbox1.match(close_regex); 
                  
                  if(opened_tags !== null){
                    var number_opened_tags = opened_tags.length; 
                  }
                  
                  if(closed_tags !== null){
                    var number_closed_tags = closed_tags.length; 
                  }
                  
                  if(number_opened_tags == number_closed_tags){            
                    $('.error').remove();
                    $('.editOptions').slideUp();
                   
                  //if the number of opened tags does not equal the number of closed tags, an error should be shown
                  }else{                
                    event.preventDefault();
                    $('.error').remove();
                    $('.editOptions').append('<p class="error">' + mw.msg('submit-error-message') + '</p>');
                    $('.error').fadeOut(5000);
                  }             
                });
            }
        } );
    }
}( mediaWiki, jQuery ) );
