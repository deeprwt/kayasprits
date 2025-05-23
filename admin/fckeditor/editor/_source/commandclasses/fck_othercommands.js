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
 * Definition of other commands that are not available internaly in the
 * browser (see FCKNamedCommand).
 */

// ### General Dialog Box Commands.
var FCKDialogCommand = function( name, title, url, width, height, getStateFunction, getStateParam )
{
	this.Name	= name ;
	this.Title	= title ;
	this.Url	= url ;
	this.Width	= width ;
	this.Height	= height ;

	this.GetStateFunction	= getStateFunction ;
	this.GetStateParam		= getStateParam ;

	this.Resizable = false ;
}

FCKDialogCommand.prototype.Execute = function()
{
	FCKDialog.OpenDialog( 'FCKDialog_' + this.Name , this.Title, this.Url, this.Width, this.Height, null, null, this.Resizable ) ;
}

FCKDialogCommand.prototype.GetState = function()
{
	if ( this.GetStateFunction )
		return this.GetStateFunction( this.GetStateParam ) ;
	else
		return FCK_TRISTATE_OFF ;
}

// Generic Undefined command (usually used when a command is under development).
var FCKUndefinedCommand = function()
{
	this.Name = 'Undefined' ;
}

FCKUndefinedCommand.prototype.Execute = function()
{
	alert( FCKLang.NotImplemented ) ;
}

FCKUndefinedCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}


// ### FormatBlock
var FCKFormatBlockCommand = function()
{}

FCKFormatBlockCommand.prototype = 
{
	Name : 'FormatBlock',
	
	Execute : FCKStyleCommand.prototype.Execute,
	
	GetState : function()
	{
		return FCK.EditorDocument ? FCK_TRISTATE_OFF : FCK_TRISTATE_DISABLED ;
	}
};

// ### FontName

var FCKFontNameCommand = function()
{}

FCKFontNameCommand.prototype = 
{
	Name		: 'FontName',
	Execute		: FCKStyleCommand.prototype.Execute,
	GetState	: FCKFormatBlockCommand.prototype.GetState
};

// ### FontSize
var FCKFontSizeCommand = function()
{}

FCKFontSizeCommand.prototype = 
{
	Name		: 'FontSize',
	Execute		: FCKStyleCommand.prototype.Execute,
	GetState	: FCKFormatBlockCommand.prototype.GetState
};

// ### Preview
var FCKPreviewCommand = function()
{
	this.Name = 'Preview' ;
}

FCKPreviewCommand.prototype.Execute = function()
{
     FCK.Preview() ;
}

FCKPreviewCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}

// ### Save
var FCKSaveCommand = function()
{
	this.Name = 'Save' ;
}

FCKSaveCommand.prototype.Execute = function()
{
	// Get the linked field form.
	var oForm = FCK.GetParentForm() ;

	if ( typeof( oForm.onsubmit ) == 'function' )
	{
		var bRet = oForm.onsubmit() ;
		if ( bRet != null && bRet === false )
			return ;
	}

	// Submit the form.
	// If there's a button named "submit" then the form.submit() function is masked and
	// can't be called in Mozilla, so we call the click() method of that button.
	if ( typeof( oForm.submit ) == 'function' )
		oForm.submit() ;
	else
		oForm.submit.click() ;
}

FCKSaveCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}

// ### NewPage
var FCKNewPageCommand = function()
{
	this.Name = 'NewPage' ;
}

FCKNewPageCommand.prototype.Execute = function()
{
	FCKUndo.SaveUndoStep() ;
	FCK.SetData( '' ) ;
	FCKUndo.Typing = true ;
	FCK.Focus() ;
}

FCKNewPageCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}

// ### Source button
var FCKSourceCommand = function()
{
	this.Name = 'Source' ;
}

FCKSourceCommand.prototype.Execute = function()
{
	if ( FCKConfig.SourcePopup )	// Until v2.2, it was mandatory for FCKBrowserInfo.IsGecko.
	{
		var iWidth	= FCKConfig.ScreenWidth * 0.65 ;
		var iHeight	= FCKConfig.ScreenHeight * 0.65 ;
		FCKDialog.OpenDialog( 'FCKDialog_Source', FCKLang.Source, 'dialog/fck_source.html', iWidth, iHeight, null, null, true ) ;
	}
	else
	    FCK.SwitchEditMode() ;
}

FCKSourceCommand.prototype.GetState = function()
{
	return ( FCK.EditMode == FCK_EDITMODE_WYSIWYG ? FCK_TRISTATE_OFF : FCK_TRISTATE_ON ) ;
}

// ### Undo
var FCKUndoCommand = function()
{
	this.Name = 'Undo' ;
}

FCKUndoCommand.prototype.Execute = function()
{
	FCKUndo.Undo() ;
}

FCKUndoCommand.prototype.GetState = function()
{
	return ( FCKUndo.CheckUndoState() ? FCK_TRISTATE_OFF : FCK_TRISTATE_DISABLED ) ;
}

// ### Redo
var FCKRedoCommand = function()
{
	this.Name = 'Redo' ;
}

FCKRedoCommand.prototype.Execute = function()
{
	FCKUndo.Redo() ;
}

FCKRedoCommand.prototype.GetState = function()
{
	return ( FCKUndo.CheckRedoState() ? FCK_TRISTATE_OFF : FCK_TRISTATE_DISABLED ) ;
}

// ### Page Break
var FCKPageBreakCommand = function()
{
	this.Name = 'PageBreak' ;
}

FCKPageBreakCommand.prototype.Execute = function()
{
	// Take an undo snapshot before changing the document
	FCKUndo.SaveUndoStep() ;

//	var e = FCK.EditorDocument.createElement( 'CENTER' ) ;
//	e.style.pageBreakAfter = 'always' ;

	// Tidy was removing the empty CENTER tags, so the following solution has
	// been found. It also validates correctly as XHTML 1.0 Strict.
	var e = FCK.EditorDocument.createElement( 'DIV' ) ;
	e.style.pageBreakAfter = 'always' ;
	e.innerHTML = '<span style="DISPLAY:none">&nbsp;</span>' ;

	var oFakeImage = FCKDocumentProcessor_CreateFakeImage( 'FCK__PageBreak', e ) ;
	FCK.InsertElement( oFakeImage ) ;
}

FCKPageBreakCommand.prototype.GetState = function()
{
	return 0 ; // FCK_TRISTATE_OFF
}

// FCKUnlinkCommand - by Johnny Egeland (johnny@coretrek.com)
var FCKUnlinkCommand = function()
{
	this.Name = 'Unlink' ;
}

FCKUnlinkCommand.prototype.Execute = function()
{
	// Take an undo snapshot before changing the document
	FCKUndo.SaveUndoStep() ;

	if ( FCKBrowserInfo.IsGeckoLike )
	{
		var oLink = FCK.Selection.MoveToAncestorNode( 'A' ) ;
		// The unlink command can generate a span in Firefox, so let's do it our way. See #430
		if ( oLink )
			FCKTools.RemoveOuterTags( oLink ) ;

		return ;
	}
	
	FCK.ExecuteNamedCommand( this.Name ) ;
}

FCKUnlinkCommand.prototype.GetState = function()
{
	var state = FCK.GetNamedCommandState( this.Name ) ;

	// Check that it isn't an anchor
	if ( state == FCK_TRISTATE_OFF && FCK.EditMode == FCK_EDITMODE_WYSIWYG )
	{
		var oLink = FCKSelection.MoveToAncestorNode( 'A' ) ;
		var bIsAnchor = ( oLink && oLink.name.length > 0 && oLink.href.length == 0 ) ;
		if ( bIsAnchor )
			state = FCK_TRISTATE_DISABLED ;
	}

	return state ;
}

// FCKSelectAllCommand
var FCKSelectAllCommand = function()
{
	this.Name = 'SelectAll' ;
}

FCKSelectAllCommand.prototype.Execute = function()
{
	if ( FCK.EditMode == FCK_EDITMODE_WYSIWYG )
	{
		FCK.ExecuteNamedCommand( 'SelectAll' ) ;
	}
	else
	{
		// Select the contents of the textarea
		var textarea = FCK.EditingArea.Textarea ;
		if ( FCKBrowserInfo.IsIE )
		{
			textarea.createTextRange().execCommand( 'SelectAll' ) ;
		}
		else
		{
			textarea.selectionStart = 0 ;
			textarea.selectionEnd = textarea.value.length ;
		}
		textarea.focus() ;
	}
}

FCKSelectAllCommand.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF ;
}

// FCKPasteCommand
var FCKPasteCommand = function()
{
	this.Name = 'Paste' ;
}

FCKPasteCommand.prototype =
{
	Execute : function()
	{
		if ( FCKBrowserInfo.IsIE )
			FCK.Paste() ;
		else
			FCK.ExecuteNamedCommand( 'Paste' ) ;
	},

	GetState : function()
	{
		return FCK.GetNamedCommandState( 'Paste' ) ;
	}
} ;

// FCKRuleCommand
var FCKRuleCommand = function()
{
	this.Name = 'Rule' ;
}

FCKRuleCommand.prototype =
{
	Execute : function()
	{
		FCKUndo.SaveUndoStep() ;
		FCK.InsertElement( 'hr' ) ;
	},

	GetState : function()
	{
		return FCK.GetNamedCommandState( 'InsertHorizontalRule' ) ;
	}
} ;

// FCKCopyCommand
var FCKCopyCommand = function()
{
	this.Name = 'Copy' ;
}

FCKCopyCommand.prototype = 
{
	Execute : function()
	{
		FCK.ExecuteNamedCommand( this.Name ) ;
	},

	GetState : function()
	{
		// Strangely, the cut command happens to have the correct states for both Copy and Cut in all browsers.
		return FCK.GetNamedCommandState( 'Cut' ) ;
	}
};

var FCKAnchorDeleteCommand = function()
{
	this.Name = 'AnchorDelete' ;
}

FCKAnchorDeleteCommand.prototype = 
{
	Execute : function()
	{
		if (FCK.Selection.GetType() == 'Control')
		{
			FCK.Selection.Delete();
		}
		else
		{
			var oFakeImage = FCK.Selection.GetSelectedElement() ;
			if ( oFakeImage )
			{
				if ( oFakeImage.tagName == 'IMG' && oFakeImage.getAttribute('_fckanchor') )
					oAnchor = FCK.GetRealElement( oFakeImage ) ;
				else
					oFakeImage = null ;
			}

			//Search for a real anchor
			if ( !oFakeImage )
			{
				oAnchor = FCK.Selection.MoveToAncestorNode( 'A' ) ;
				if ( oAnchor )
					FCK.Selection.SelectNode( oAnchor ) ;
			}

			// If it's also a link, then just remove the name and exit
			if ( oAnchor.href.length != 0 )
			{
				oAnchor.removeAttribute( 'name' ) ;
				// Remove temporary class for IE
				if ( FCKBrowserInfo.IsIE )
					oAnchor.className = oAnchor.className.replace( FCKRegexLib.FCK_Class, '' ) ;
				return ;
			}

			// We need to remove the anchor
			// If we got a fake image, then just remove it and we're done
			if ( oFakeImage )
			{
				oFakeImage.parentNode.removeChild( oFakeImage ) ;
				return ;
			}
			// Empty anchor, so just remove it
			if ( oAnchor.innerHTML.length == 0 )
			{
				oAnchor.parentNode.removeChild( oAnchor ) ;
				return ;
			}
			// Anchor with content, leave the content
			FCKTools.RemoveOuterTags( oAnchor ) ;
		}
		if ( FCKBrowserInfo.IsGecko )
			FCK.Selection.Collapse( true ) ;
	},

	GetState : function()
	{
		return FCK.GetNamedCommandState( 'Unlink') ;
	}
};
