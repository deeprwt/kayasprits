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
 * Class for working with a selection range, much like the W3C DOM Range, but
 * it is not intended to be an implementation of the W3C interface.
 */

var FCKDomRange = function( sourceWindow )
{
	this.Window = sourceWindow ;
	this._Cache = {} ;
}

FCKDomRange.prototype =
{

	_UpdateElementInfo : function()
	{
		var innerRange = this._Range ;

		if ( !innerRange )
			this.Release( true ) ;
		else
		{
			// For text nodes, the node itself is the StartNode.
			var eStart	= innerRange.startContainer ;
			var eEnd	= innerRange.endContainer ;

			var oElementPath = new FCKElementPath( eStart ) ;
			this.StartNode			= eStart.nodeType == 3 ? eStart : eStart.childNodes[ innerRange.startOffset ] ;
			this.StartContainer		= eStart ;
			this.StartBlock			= oElementPath.Block ;
			this.StartBlockLimit	= oElementPath.BlockLimit ;

			if ( eStart != eEnd )
				oElementPath = new FCKElementPath( eEnd ) ;

			// The innerRange.endContainer[ innerRange.endOffset ] is not
			// usually part of the range, but the marker for the range end. So,
			// let's get the previous available node as the real end.
			var eEndNode = eEnd ;
			if ( innerRange.endOffset == 0 )
			{
				while ( eEndNode && !eEndNode.previousSibling )
					eEndNode = eEndNode.parentNode ;

				if ( eEndNode )
					eEndNode = eEndNode.previousSibling ;
			}
			else if ( eEndNode.nodeType == 1 )
				eEndNode = eEndNode.childNodes[ innerRange.endOffset - 1 ] ;

			this.EndNode			= eEndNode ;
			this.EndContainer		= eEnd ;
			this.EndBlock			= oElementPath.Block ;
			this.EndBlockLimit		= oElementPath.BlockLimit ;
		}

		this._Cache = {} ;
	},

	CreateRange : function()
	{
		return new FCKW3CRange( this.Window.document ) ;
	},

	DeleteContents : function()
	{
		if ( this._Range )
		{
			this._Range.deleteContents() ;
			this._UpdateElementInfo() ;
		}
	},

	ExtractContents : function()
	{
		if ( this._Range )
		{
			var docFrag = this._Range.extractContents() ;
			this._UpdateElementInfo() ;
			return docFrag ;
		}
	},

	CheckIsCollapsed : function()
	{
		if ( this._Range )
			return this._Range.collapsed ;
	},

	Collapse : function( toStart )
	{
		if ( this._Range )
			this._Range.collapse( toStart ) ;

		this._UpdateElementInfo() ;
	},

	Clone : function()
	{
		var oClone = FCKTools.CloneObject( this ) ;

		if ( this._Range )
			oClone._Range = this._Range.cloneRange() ;

		return oClone ;
	},

	MoveToNodeContents : function( targetNode )
	{
		if ( !this._Range )
			this._Range = this.CreateRange() ;

		this._Range.selectNodeContents( targetNode ) ;

		this._UpdateElementInfo() ;
	},

	MoveToElementStart : function( targetElement )
	{
		this.SetStart(targetElement,1) ;
		this.SetEnd(targetElement,1) ;
	},

	// Moves to the first editing point inside a element. For example, in a
	// element tree like "<p><b><i></i></b> Text</p>", the start editing point
	// is "<p><b><i>^</i></b> Text</p>" (inside <i>).
	MoveToElementEditStart : function( targetElement )
	{
		var child ;

		while ( ( child = targetElement.firstChild ) && child.nodeType == 1 && FCKListsLib.EmptyElements[ child.nodeName.toLowerCase() ] == null )
			targetElement = child ;

		this.MoveToElementStart( targetElement ) ;
	},

	InsertNode : function( node )
	{
		if ( this._Range )
			this._Range.insertNode( node ) ;
	},

	CheckIsEmpty : function()
	{
		if ( this.CheckIsCollapsed() )
			return true ;

		// Inserts the contents of the range in a div tag.
		var eToolDiv = this.Window.document.createElement( 'div' ) ;
		this._Range.cloneContents().AppendTo( eToolDiv ) ;

		FCKDomTools.TrimNode( eToolDiv ) ;

		return ( eToolDiv.innerHTML.length == 0 ) ;
	},

	CheckStartOfBlock : function()
	{
		var bIsStartOfBlock = this._Cache.IsStartOfBlock ;

		if ( bIsStartOfBlock != undefined )
			return bIsStartOfBlock ;

		// Create a clone of the current range.
		var oTestRange = this.Clone() ;

		// Collapse it to its start point.
		oTestRange.Collapse( true ) ;

		// Move the start boundary to the start of the block.
		oTestRange.SetStart( oTestRange.StartBlock || oTestRange.StartBlockLimit, 1 ) ;

		if ( oTestRange.CheckIsCollapsed() )
			bIsStartOfBlock = true ;
		else
		{
			// Inserts the contents of the range in a div tag.
			var eToolDiv = oTestRange.Window.document.createElement( 'div' ) ;
			oTestRange._Range.cloneContents().AppendTo( eToolDiv ) ;

			// This line is why we don't use CheckIsEmpty() here...
			// Because using RTrimNode() or TrimNode() would be incorrect - 
			// TrimNode() and RTrimNode() would delete <br> nodes at the end of the div node,
			// but for checking start of block they are actually meaningful. (Bug #1350)
			FCKDomTools.LTrimNode( eToolDiv ) ;

			bIsStartOfBlock = ( eToolDiv.innerHTML.length == 0 ) ;
		}

		oTestRange.Release() ;

		return ( this._Cache.IsStartOfBlock = bIsStartOfBlock ) ;
	},

	CheckEndOfBlock : function( refreshSelection )
	{
		var bIsEndOfBlock = this._Cache.IsEndOfBlock ;

		if ( bIsEndOfBlock != undefined )
			return bIsEndOfBlock ;

		// Create a clone of the current range.
		var oTestRange = this.Clone() ;

		// Collapse it to its end point.
		oTestRange.Collapse( false ) ;

		// Move the end boundary to the end of the block.
		oTestRange.SetEnd( oTestRange.EndBlock || oTestRange.EndBlockLimit, 2 ) ;

		bIsEndOfBlock = oTestRange.CheckIsCollapsed() ;

		if ( !bIsEndOfBlock )
		{
			// Inserts the contents of the range in a div tag.
			var eToolDiv = this.Window.document.createElement( 'div' ) ;
			oTestRange._Range.cloneContents().AppendTo( eToolDiv ) ;
			FCKDomTools.TrimNode( eToolDiv ) ;

			// Find out if we are in an empty tree of inline elements, like <b><i><span></span></i></b>
			bIsEndOfBlock = true ;
			var eLastChild = eToolDiv ;
			while ( ( eLastChild = eLastChild.lastChild ) )
			{
				// Check the following:
				//		1. Is there more than one node in the parents children?
				//		2. Is the node not an element node?
				//		3. Is it not a inline element.
				if ( eLastChild.previousSibling || eLastChild.nodeType != 1 || FCKListsLib.InlineChildReqElements[ eLastChild.nodeName.toLowerCase() ] == null )
				{
					// So we are not in the end of the range.
					bIsEndOfBlock = false ;
					break ;
				}
			}
		}

		oTestRange.Release() ;

		if ( refreshSelection )
			this.Select() ;

		return this._Cache.IsEndOfBlock = bIsEndOfBlock ;
	},

	// This is an "intrusive" way to create a bookmark. It includes <span> tags
	// in the range boundaries. The advantage of it is that it is possible to
	// handle DOM mutations when moving back to the bookmark.
	// Attention: the inclusion of nodes in the DOM is a design choice and
	// should not be changed as there are other points in the code that may be
	// using those nodes to perform operations. See GetBookmarkNode.
	// For performance, includeNodes=true if intended to SelectBookmark.
	CreateBookmark : function( includeNodes )
	{
		// Create the bookmark info (random IDs).
		var oBookmark =
		{
			StartId	: (new Date()).valueOf() + Math.floor(Math.random()*1000) + 'S',
			EndId	: (new Date()).valueOf() + Math.floor(Math.random()*1000) + 'E'
		} ;

		var oDoc = this.Window.document ;
		var eStartSpan ;
		var eEndSpan ;
		var oClone ;

		// For collapsed ranges, add just the start marker.
		if ( !this.CheckIsCollapsed() )
		{
			eEndSpan = oDoc.createElement( 'span' ) ;
			eEndSpan.style.display = 'none' ;
			eEndSpan.id = oBookmark.EndId ;
			eEndSpan.setAttribute( '_fck_bookmark', true ) ;

			// For IE, it must have something inside, otherwise it may be
			// removed during DOM operations.
//			if ( FCKBrowserInfo.IsIE )
				eEndSpan.innerHTML = '&nbsp;' ;

			oClone = this.Clone() ;
			oClone.Collapse( false ) ;
			oClone.InsertNode( eEndSpan ) ;
		}

		eStartSpan = oDoc.createElement( 'span' ) ;
		eStartSpan.style.display = 'none' ;
		eStartSpan.id = oBookmark.StartId ;
		eStartSpan.setAttribute( '_fck_bookmark', true ) ;

		// For IE, it must have something inside, otherwise it may be removed
		// during DOM operations.
//		if ( FCKBrowserInfo.IsIE )
			eStartSpan.innerHTML = '&nbsp;' ;

		oClone = this.Clone() ;
		oClone.Collapse( true ) ;
		oClone.InsertNode( eStartSpan ) ;

		if ( includeNodes )
		{
			oBookmark.StartNode = eStartSpan ;
			oBookmark.EndNode = eEndSpan ;
		}
		
		// Update the range position.
		if ( eEndSpan )
		{
			this.SetStart( eStartSpan, 4 ) ;
			this.SetEnd( eEndSpan, 3 ) ;
		}
		else
			this.MoveToPosition( eStartSpan, 4 ) ;
		
		return oBookmark ;
	},

	// This one should be a part of a hypothetic "bookmark" object.
	GetBookmarkNode : function( bookmark, start )
	{
		var doc = this.Window.document ;

		if ( start )
			return bookmark.StartNode || doc.getElementById( bookmark.StartId ) ;
		else
			return bookmark.EndNode || doc.getElementById( bookmark.EndId ) ;
	},

	MoveToBookmark : function( bookmark, preserveBookmark )
	{
		var eStartSpan	= this.GetBookmarkNode( bookmark, true ) ;
		var eEndSpan	= this.GetBookmarkNode( bookmark, false ) ;

		this.SetStart( eStartSpan, 3 ) ;

		if ( !preserveBookmark )
			FCKDomTools.RemoveNode( eStartSpan ) ;

		// If collapsed, the end span will not be available.
		if ( eEndSpan )
		{
			this.SetEnd( eEndSpan, 3 ) ;

			if ( !preserveBookmark )
				FCKDomTools.RemoveNode( eEndSpan ) ;
		}
		else
			this.Collapse( true ) ;

		this._UpdateElementInfo() ;
	},

	// Non-intrusive bookmark algorithm
	CreateBookmark2 : function()
	{
		// If there is no range then get out of here.
		// It happens on initial load in Safari #962 and if the editor it's hidden also in Firefox
		if ( ! this._Range )
			return { "Start" : 0, "End" : 0 } ;

		// First, we record down the offset values
		var bookmark =
		{
			"Start" : [ this._Range.startOffset ],
			"End" : [ this._Range.endOffset ]
		} ;
		var curStart = this._Range.startContainer.previousSibling ;
		var curEnd = this._Range.endContainer.previousSibling ;
		while ( curStart && curStart.nodeType == 3 )
		{
			bookmark.Start[0] += curStart.length ;
			curStart = curStart.previousSibling ;
		}
		while ( curEnd && curEnd.nodeType == 3 )
		{
			bookmark.End[0] += curEnd.length ;
			curEnd = curEnd.previousSibling ;
		}
		// Then, we record down the precise position of the container nodes
		// by walking up the DOM tree and counting their childNode index
		bookmark.Start = FCKDomTools.GetNodeAddress( this._Range.startContainer, true ).concat( bookmark.Start ) ;
		bookmark.End = FCKDomTools.GetNodeAddress( this._Range.endContainer, true ).concat( bookmark.End ) ;
		return bookmark;
	},

	MoveToBookmark2 : function( bookmark )
	{
		// Reverse the childNode counting algorithm in CreateBookmark2()
		var curStart = FCKDomTools.GetNodeFromAddress( this.Window.document, bookmark.Start.slice( 0, -1 ), true ) ;
		var curEnd = FCKDomTools.GetNodeFromAddress( this.Window.document, bookmark.End.slice( 0, -1 ), true ) ;

		// Generate the W3C Range object and update relevant data
		this.Release( true ) ;
		this._Range = new FCKW3CRange( this.Window.document ) ;
		var startOffset = bookmark.Start[ bookmark.Start.length - 1 ] ;
		var endOffset = bookmark.End[ bookmark.End.length - 1 ] ;
		while ( curStart.nodeType == 3 && startOffset > curStart.length )
		{
			if ( ! curStart.nextSibling || curStart.nextSibling.nodeType != 3 )
				break ;
			startOffset -= curStart.length ;
			curStart = curStart.nextSibling ;
		}
		while ( curEnd.nodeType == 3 && endOffset > curEnd.length )
		{
			if ( ! curEnd.nextSibling || curEnd.nextSibling.nodeType != 3 )
				break ;
			endOffset -= curEnd.length ;
			curEnd = curEnd.nextSibling ;
		}
		this._Range.setStart( curStart, startOffset ) ;
		this._Range.setEnd( curEnd, endOffset ) ;
		this._UpdateElementInfo() ;
	},

	MoveToPosition : function( targetElement, position )
	{
		this.SetStart( targetElement, position ) ;
		this.Collapse( true ) ;
	},

	/*
	 * Moves the position of the start boundary of the range to a specific position
	 * relatively to a element.
	 *		@position:
	 *			1 = After Start		<target>^contents</target>
	 *			2 = Before End		<target>contents^</target>
	 *			3 = Before Start	^<target>contents</target>
	 *			4 = After End		<target>contents</target>^
	 */
	SetStart : function( targetElement, position, noInfoUpdate )
	{
		var oRange = this._Range ;
		if ( !oRange )
			oRange = this._Range = this.CreateRange() ;

		switch( position )
		{
			case 1 :		// After Start		<target>^contents</target>
				oRange.setStart( targetElement, 0 ) ;
				break ;

			case 2 :		// Before End		<target>contents^</target>
				oRange.setStart( targetElement, targetElement.childNodes.length ) ;
				break ;

			case 3 :		// Before Start		^<target>contents</target>
				oRange.setStartBefore( targetElement ) ;
				break ;

			case 4 :		// After End		<target>contents</target>^
				oRange.setStartAfter( targetElement ) ;
		}

		if ( !noInfoUpdate )
			this._UpdateElementInfo() ;
	},

	/*
	 * Moves the position of the start boundary of the range to a specific position
	 * relatively to a element.
	 *		@position:
	 *			1 = After Start		<target>^contents</target>
	 *			2 = Before End		<target>contents^</target>
	 *			3 = Before Start	^<target>contents</target>
	 *			4 = After End		<target>contents</target>^
	 */
	SetEnd : function( targetElement, position, noInfoUpdate )
	{
		var oRange = this._Range ;
		if ( !oRange )
			oRange = this._Range = this.CreateRange() ;

		switch( position )
		{
			case 1 :		// After Start		<target>^contents</target>
				oRange.setEnd( targetElement, 0 ) ;
				break ;

			case 2 :		// Before End		<target>contents^</target>
				oRange.setEnd( targetElement, targetElement.childNodes.length ) ;
				break ;

			case 3 :		// Before Start		^<target>contents</target>
				oRange.setEndBefore( targetElement ) ;
				break ;

			case 4 :		// After End		<target>contents</target>^
				oRange.setEndAfter( targetElement ) ;
		}

		if ( !noInfoUpdate )
			this._UpdateElementInfo() ;
	},

	Expand : function( unit )
	{
		var oNode, oSibling ;

		switch ( unit )
		{
			// Expand the range to include all inline parent elements if we are
			// are in their boundary limits.
			// For example (where [ ] are the range limits):
			//	Before =>		Some <b>[<i>Some sample text]</i></b>.
			//	After =>		Some [<b><i>Some sample text</i></b>].
			case 'inline_elements' :
				// Expand the start boundary.
				if ( this._Range.startOffset == 0 )
				{
					oNode = this._Range.startContainer ;

					if ( oNode.nodeType != 1 )
						oNode = oNode.previousSibling ? null : oNode.parentNode ;
					
					if ( oNode )
					{
						while ( FCKListsLib.InlineNonEmptyElements[ oNode.nodeName.toLowerCase() ] )
						{
							this._Range.setStartBefore( oNode ) ;

							if ( oNode != oNode.parentNode.firstChild )
								break ;

							oNode = oNode.parentNode ;
						}
					}
				}

				// Expand the end boundary.
				oNode = this._Range.endContainer ;
				var offset = this._Range.endOffset ;

				if ( ( oNode.nodeType == 3 && offset >= oNode.nodeValue.length ) || ( oNode.nodeType == 1 && offset >= oNode.childNodes.length ) || ( oNode.nodeType != 1 && oNode.nodeType != 3 ) )
				{
					if ( oNode.nodeType != 1 )
						oNode = oNode.nextSibling ? null : oNode.parentNode ;

					if ( oNode )
					{
						while ( FCKListsLib.InlineNonEmptyElements[ oNode.nodeName.toLowerCase() ] )
						{
							this._Range.setEndAfter( oNode ) ;

							if ( oNode != oNode.parentNode.lastChild )
								break ;

							oNode = oNode.parentNode ;
						}
					}
				}

				break ;

			case 'block_contents' :
			case 'list_contents' :
				var boundarySet = FCKListsLib.BlockBoundaries ;
				if ( unit == 'list_contents' || FCKConfig.EnterMode == 'br' )
					boundarySet = FCKListsLib.ListBoundaries ;

				if ( this.StartBlock && FCKConfig.EnterMode != 'br' && unit == 'block_contents' )
					this.SetStart( this.StartBlock, 1 ) ;
				else
				{
					// Get the start node for the current range.
					oNode = this._Range.startContainer ;

					// If it is an element, get the node right before of it (in source order).
					if ( oNode.nodeType == 1 )
					{
						var lastNode = oNode.childNodes[ this._Range.startOffset ] ;
						if ( lastNode )
							oNode = FCKDomTools.GetPreviousSourceNode( lastNode, true ) ;
						else
							oNode = oNode.lastChild || oNode ;
					}

					// We must look for the left boundary, relative to the range
					// start, which is limited by a block element.
					while ( oNode
							&& ( oNode.nodeType != 1
								|| ( oNode != this.StartBlockLimit
									&& !boundarySet[ oNode.nodeName.toLowerCase() ] ) ) )
					{
						this._Range.setStartBefore( oNode ) ;
						oNode = oNode.previousSibling || oNode.parentNode ;
					}
				}

				if ( this.EndBlock && FCKConfig.EnterMode != 'br' && unit == 'block_contents' && this.EndBlock.nodeName.toLowerCase() != 'li' )
					this.SetEnd( this.EndBlock, 2 ) ;
				else
				{
					oNode = this._Range.endContainer ;
					if ( oNode.nodeType == 1 )
						oNode = oNode.childNodes[ this._Range.endOffset ] || oNode.lastChild ;

					// We must look for the right boundary, relative to the range
					// end, which is limited by a block element.
					while ( oNode
							&& ( oNode.nodeType != 1
								|| ( oNode != this.StartBlockLimit
									&& !boundarySet[ oNode.nodeName.toLowerCase() ] ) ) )
					{
						this._Range.setEndAfter( oNode ) ;
						oNode = oNode.nextSibling || oNode.parentNode ;
					}

					// In EnterMode='br', the end <br> boundary element must
					// be included in the expanded range.
					if ( oNode && oNode.nodeName.toLowerCase() == 'br' )
						this._Range.setEndAfter( oNode ) ;
				}

				this._UpdateElementInfo() ;
		}
	},

	/**
	 * Split the block element for the current range. It deletes the contents
	 * of the range and splits the block in the collapsed position, resulting
	 * in two sucessive blocks. The range is then positioned in the middle of
	 * them.
	 *
	 * It returns and object with the following properties:
	 *		- PreviousBlock	: a reference to the block element that preceeds
	 *		  the range after the split.
	 *		- NextBlock : a reference to the block element that preceeds the
	 *		  range after the split.
	 *		- WasStartOfBlock : a boolean indicating that the range was
	 *		  originaly at the start of the block.
	 *		- WasEndOfBlock : a boolean indicating that the range was originaly
	 *		  at the end of the block.
	 *
	 * If the range was originaly at the start of the block, no split will happen
	 * and the PreviousBlock value will be null. The same is valid for the
	 * NextBlock value if the range was at the end of the block.
	 */
	SplitBlock : function()
	{
		if ( !this._Range )
			this.MoveToSelection() ;

		// The range boundaries must be in the same "block limit" element.
		if ( this.StartBlockLimit == this.EndBlockLimit )
		{
			// Get the current blocks.
			var eStartBlock		= this.StartBlock ;
			var eEndBlock		= this.EndBlock ;

			if ( FCKConfig.EnterMode != 'br' )
			{
				if ( !eStartBlock )
				{
					eStartBlock = this.FixBlock( true ) ;
					eEndBlock	= this.EndBlock ;	// FixBlock may have fixed the EndBlock too.
				}

				if ( !eEndBlock )
					eEndBlock = this.FixBlock( false ) ;
			}

			// Get the range position.
			var bIsStartOfBlock	= ( eStartBlock != null && this.CheckStartOfBlock() ) ;
			var bIsEndOfBlock	= ( eEndBlock != null && this.CheckEndOfBlock() ) ;

			// Delete the current contents.
			if ( !this.CheckIsEmpty() )
				this.DeleteContents() ;

			if ( eStartBlock && eEndBlock && eStartBlock == eEndBlock )
			{
				if ( bIsEndOfBlock )
				{
					this.MoveToPosition( eEndBlock, 4 ) ;
					eEndBlock = null ;
				}
				else if ( bIsStartOfBlock )
				{
					this.MoveToPosition( eStartBlock, 3 ) ;
					eStartBlock = null ;
				}
				else
				{
					// Extract the contents of the block from the selection point to the end of its contents.
					this.SetEnd( eStartBlock, 2 ) ;
					var eDocFrag = this.ExtractContents() ;

					// Duplicate the block element after it.
					eEndBlock = eStartBlock.cloneNode( false ) ;
					eEndBlock.removeAttribute( 'id', false ) ;

					// Place the extracted contents in the duplicated block.
					eDocFrag.AppendTo( eEndBlock ) ;

					FCKDomTools.InsertAfterNode( eStartBlock, eEndBlock ) ;

					this.MoveToPosition( eStartBlock, 4 ) ;

					// In Gecko, the last child node must be a bogus <br>.
					// Note: bogus <br> added under <ul> or <ol> would cause lists to be incorrectly rendered.
					if ( FCKBrowserInfo.IsGecko &&
							! eStartBlock.nodeName.IEquals( ['ul', 'ol'] ) )
						FCKTools.AppendBogusBr( eStartBlock ) ;
				}
			}

			return {
				PreviousBlock	: eStartBlock,
				NextBlock		: eEndBlock,
				WasStartOfBlock : bIsStartOfBlock,
				WasEndOfBlock	: bIsEndOfBlock
			} ;
		}

		return null ;
	},

	// Transform a block without a block tag in a valid block (orphan text in the body or td, usually).
	FixBlock : function( isStart )
	{
		// Bookmark the range so we can restore it later.
		var oBookmark = this.CreateBookmark() ;

		// Collapse the range to the requested ending boundary.
		this.Collapse( isStart ) ;

		// Expands it to the block contents.
		this.Expand( 'block_contents' ) ;

		// Create the fixed block.
		var oFixedBlock = this.Window.document.createElement( FCKConfig.EnterMode ) ;

		// Move the contents of the temporary range to the fixed block.
		this.ExtractContents().AppendTo( oFixedBlock ) ;
		FCKDomTools.TrimNode( oFixedBlock ) ;

		// Insert the fixed block into the DOM.
		this.InsertNode( oFixedBlock ) ;

		// Move the range back to the bookmarked place.
		this.MoveToBookmark( oBookmark ) ;

		return oFixedBlock ;
	},

	Release : function( preserveWindow )
	{
		if ( !preserveWindow )
			this.Window = null ;

		this.StartNode = null ;
		this.StartContainer = null ;
		this.StartBlock = null ;
		this.StartBlockLimit = null ;
		this.EndNode = null ;
		this.EndContainer = null ;
		this.EndBlock = null ;
		this.EndBlockLimit = null ;
		this._Range = null ;
		this._Cache = null ;
	},

	CheckHasRange : function()
	{
		return !!this._Range ;
	},
	
	GetTouchedStartNode : function()
	{
		var range = this._Range ;
		var container = range.startContainer ;
		
		if ( range.collapsed || container.nodeType != 1 )
			return container ;
		
		return container.childNodes[ range.startOffset ] || container ;
	},
	
	GetTouchedEndNode : function()
	{
		var range = this._Range ;
		var container = range.endContainer ;
		
		if ( range.collapsed || container.nodeType != 1 )
			return container ;
		
		return container.childNodes[ range.endOffset - 1 ] || container ;
	}
} ;
