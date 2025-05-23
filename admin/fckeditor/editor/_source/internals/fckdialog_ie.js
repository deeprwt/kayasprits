﻿/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Dialog windows operations. (IE specific implementations)
 */

FCKDialog.Show = function( dialogInfo, dialogName, pageUrl, dialogWidth, dialogHeight, parentWindow, resizable )
{
	if ( !parentWindow )
		parentWindow = window ;

	var sOptions = 'help:no;scroll:no;status:no' +
		';resizable:'  + ( resizable ? 'yes' : 'no' ) +
		';dialogWidth:' + dialogWidth + 'px' +
		';dialogHeight:' + dialogHeight + 'px' ;

	FCKFocusManager.Lock() ;

	var oReturn = 'B' ;

	try
	{
		oReturn = parentWindow.showModalDialog( pageUrl, dialogInfo, sOptions ) ;
	}
	catch( e ) {}

	if ( 'B' === oReturn )
		alert( FCKLang.DialogBlocked ) ;

	FCKFocusManager.Unlock() ;
}
