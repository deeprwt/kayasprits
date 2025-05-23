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
 * Defines the FCKXHtml object, responsible for the XHTML operations.
 * IE specific.
 */

FCKXHtml._GetMainXmlString = function()
{
	return this.MainNode.xml ;
}

FCKXHtml._AppendAttributes = function( xmlNode, htmlNode, node, nodeName )
{
	var aAttributes = htmlNode.attributes ;

	for ( var n = 0 ; n < aAttributes.length ; n++ )
	{
		var oAttribute = aAttributes[n] ;

		if ( oAttribute.specified )
		{
			var sAttName = oAttribute.nodeName.toLowerCase() ;
			var sAttValue ;

			// Ignore any attribute starting with "_fck".
			if ( sAttName.StartsWith( '_fck' ) )
				continue ;
			// The following must be done because of a bug on IE regarding the style
			// attribute. It returns "null" for the nodeValue.
			else if ( sAttName == 'style' )
			{
				var data = FCKTools.ProtectFormStyles( htmlNode ) ;
				sAttValue = htmlNode.style.cssText.replace( FCKRegexLib.StyleProperties, FCKTools.ToLowerCase ) ;
				FCKTools.RestoreFormStyles( htmlNode, data ) ;
			}
			// There are two cases when the oAttribute.nodeValue must be used:
			//		- for the "class" attribute
			//		- for events attributes (on IE only).
			else if ( sAttName == 'class' )
			{
				sAttValue = oAttribute.nodeValue.replace( FCKRegexLib.FCK_Class, '' ) ;
				if ( sAttValue.length == 0 )
					continue ;
			}
			else if ( sAttName.indexOf('on') == 0 )
				sAttValue = oAttribute.nodeValue ;
			else if ( nodeName == 'body' && sAttName == 'contenteditable' )
				continue ;
			// XHTML doens't support attribute minimization like "CHECKED". It must be transformed to checked="checked".
			else if ( oAttribute.nodeValue === true )
				sAttValue = sAttName ;
			else
			{
				// We must use getAttribute to get it exactly as it is defined.
				// There are some rare cases that IE throws an error here, so we must try/catch.
				try
				{
					sAttValue = htmlNode.getAttribute( sAttName, 2 ) ;
				}
				catch (e) {}
			}
			this._AppendAttribute( node, sAttName, sAttValue || oAttribute.nodeValue ) ;
		}
	}
}

FCKXHtml.TagProcessors['meta'] = function( node, htmlNode )
{
	var oHttpEquiv = node.attributes.getNamedItem( 'http-equiv' ) ;

	if ( oHttpEquiv == null || oHttpEquiv.value.length == 0 )
	{
		// Get the http-equiv value from the outerHTML.
		var sHttpEquiv = htmlNode.outerHTML.match( FCKRegexLib.MetaHttpEquiv ) ;

		if ( sHttpEquiv )
		{
			sHttpEquiv = sHttpEquiv[1] ;
			FCKXHtml._AppendAttribute( node, 'http-equiv', sHttpEquiv ) ;
		}
	}

	return node ;
}

// IE automatically changes <FONT> tags to <FONT size=+0>.
FCKXHtml.TagProcessors['font'] = function( node, htmlNode )
{
	if ( node.attributes.length == 0 )
		node = FCKXHtml.XML.createDocumentFragment() ;

	node = FCKXHtml._AppendChildNodes( node, htmlNode ) ;

	return node ;
}

// IE doens't see the value attribute as an attribute for the <INPUT> tag.
FCKXHtml.TagProcessors['input'] = function( node, htmlNode )
{
	if ( htmlNode.name )
		FCKXHtml._AppendAttribute( node, 'name', htmlNode.name ) ;

	if ( htmlNode.value && !node.attributes.getNamedItem( 'value' ) )
		FCKXHtml._AppendAttribute( node, 'value', htmlNode.value ) ;

	if ( !node.attributes.getNamedItem( 'type' ) )
		FCKXHtml._AppendAttribute( node, 'type', 'text' ) ;

	return node ;
}

// IE ignores the "SELECTED" attribute so we must add it manually.
FCKXHtml.TagProcessors['option'] = function( node, htmlNode )
{
	if ( htmlNode.selected && !node.attributes.getNamedItem( 'selected' ) )
		FCKXHtml._AppendAttribute( node, 'selected', 'selected' ) ;

	node = FCKXHtml._AppendChildNodes( node, htmlNode ) ;

	return node ;
}

// IE ignores the "COORDS" and "SHAPE" attribute so we must add it manually.
FCKXHtml.TagProcessors['area'] = function( node, htmlNode )
{
	if ( ! node.attributes.getNamedItem( 'coords' ) )
	{
		var sCoords = htmlNode.getAttribute( 'coords', 2 ) ;
		if ( sCoords && sCoords != '0,0,0' )
			FCKXHtml._AppendAttribute( node, 'coords', sCoords ) ;
	}

	if ( ! node.attributes.getNamedItem( 'shape' ) )
	{
		var sShape = htmlNode.getAttribute( 'shape', 2 ) ;
		if ( sShape && sShape.length > 0 )
			FCKXHtml._AppendAttribute( node, 'shape', sShape.toLowerCase() ) ;
	}

	return node ;
}

FCKXHtml.TagProcessors['label'] = function( node, htmlNode )
{
	if ( htmlNode.htmlFor.length > 0 )
		FCKXHtml._AppendAttribute( node, 'for', htmlNode.htmlFor ) ;

	node = FCKXHtml._AppendChildNodes( node, htmlNode ) ;

	return node ;
}

FCKXHtml.TagProcessors['form'] = function( node, htmlNode )
{
	if ( htmlNode.acceptCharset && htmlNode.acceptCharset.length > 0 && htmlNode.acceptCharset != 'UNKNOWN' )
		FCKXHtml._AppendAttribute( node, 'accept-charset', htmlNode.acceptCharset ) ;

	// IE has a bug and htmlNode.attributes['name'].specified=false if there is
	// no element with id="name" inside the form (#360 and SF-BUG-1155726).
	var nameAtt = htmlNode.attributes['name'] ;

	if ( nameAtt && nameAtt.value.length > 0 )
		FCKXHtml._AppendAttribute( node, 'name', nameAtt.value ) ;

	node = FCKXHtml._AppendChildNodes( node, htmlNode, true ) ;

	return node ;
}

// IE doens't hold the name attribute as an attribute for the <TEXTAREA> and <SELECT> tags.
FCKXHtml.TagProcessors['textarea'] = FCKXHtml.TagProcessors['select'] = function( node, htmlNode )
{
	if ( htmlNode.name )
		FCKXHtml._AppendAttribute( node, 'name', htmlNode.name ) ;

	node = FCKXHtml._AppendChildNodes( node, htmlNode ) ;

	return node ;
}

// On very rare cases, IE is loosing the "align" attribute for DIV. (right align and apply bulleted list)
FCKXHtml.TagProcessors['div'] = function( node, htmlNode )
{
	if ( htmlNode.align.length > 0 )
		FCKXHtml._AppendAttribute( node, 'align', htmlNode.align ) ;

	node = FCKXHtml._AppendChildNodes( node, htmlNode, true ) ;

	return node ;
}
