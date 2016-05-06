<?php

/**
 * This file incorporates work covered by the following copyright and
 * permission notice: 
 * 
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
 * 2015/2016: Modifications @ Arent van Korlaar <akvankorlaar 'at' gmail 'dot' com>
 */
class JBTEIToolbarHooks extends ManuscriptDeskBaseHooks {
    
    public function __construct(){
        //override parent constructor because no database calls have to be made
    }

    /**
     * Adds the modules to the edit form
     */
    public function editPageShowEditFormInitial($toolbar, OutputPage $out) {

        if (!$this->isInManuscriptsNamespace($out) || !$this->manuscriptPageExists($out)) {
            return true;
        }

        $out->addModuleStyles('ext.JBTEIToolbarcss');
        $out->addModules('ext.JBTEIToolbar');

        return true;
    }

}
