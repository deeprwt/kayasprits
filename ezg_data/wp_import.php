<?php
/*
	WordPress to EZG blog import
	http://www.ezgenerator.com
	Copyright (c) 2004-2014 Image-line

	WXR_Parser_SimpleXML class is based on WordPress Importer Plugin
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class WP_import
{
	public $page;

	public function __construct($pg)
	{
		if($pg instanceof LivePageClass)
			$this->page=$pg;
	}

	public function import($file)
	{
		global $db;

		$parser = new WXR_Parser_SimpleXML;
		$data = $parser->parse( $file );
		if(!is_array($data))
		{
			 unlink($file);
			 echo json_encode($data);
			 exit;
		}

		$users_data=User::getAllUsers(true);
		$counterAuthors=0;

		foreach($data['authors'] as $k=>$autor)
		{
			if(isset($users_data[$autor['username']])) //existing user
				$data['authors'][$k]['uid']=$users_data[$autor['username']]['uid'];
			else //add new user
			{
				$user_data=array();
				$user_data['creation_date']=Date::buildMysqlTime();
				$user_data['status']=1;
				$user_data['confirmed']=1;
				$user_data['self_registered']=1;
				$user_data['username']=$autor['username'];
				$user_data['display_name']=$autor['display_name'];

				$user_data['first_name']=$autor['last_name'];
				$user_data['surname']=$autor['last_name'];
				$user_data['email']=$autor['email'];

				$user_id=$db->query_insert('ca_users',$user_data);
				$access=array('user_id'=>$user_id,'section'=>'ALL','page_id'=>0,'access_type'=>0);
				$db->query_insert('ca_users_access',$access);

				$data['authors'][$k]['uid']=$user_id;
				$counterAuthors++;
			}
		}

		$last_cid=$this->page->categoriesModule->get_nextCategoryId();
		$counterCategories=0;

		foreach($data['categories'] as $k=>$category)
		{
			$cid=$this->page->categoriesModule->get_categoryidbyname($category['cname']);

			if($cid!==false) //existing user
				$data['categories'][$k]['cid']=$cid;
			else //add new category
			{
				$data['categories'][$k]['cid']=$last_cid++;
				$db->query_insert($this->page->data_pre.'categories',$data['categories'][$k]);
				$counterCategories++;
			}
		}
		
		$this->page->categoriesModule->build_categories_list();

		$entry_id=time();
		$counterPosts=0;
		$counterComments=0;

		foreach($data['posts'] as $post)
		{
			 $post['entry_id']=$entry_id;
			 $post['posted_by']=$data['authors'][$post['author']]['uid'];
			 $post['category']=$this->page->categoriesModule->get_categoryidbyname($post['category']);

			 if($post['publish_status']=='draft')
				  $post['publish_status']='unpublished';
			 elseif($post['publish_status']=='future')
				  $post['publish_status']='scheduled';
			 elseif($post['publish_status']=='publish')
				  $post['publish_status']='published';
			 elseif($post['publish_status']=='trash')
				  $post['publish_status']='archived';

			 $post_cleared=$post;
			 if(isset($post_cleared['comments']))
				unset($post_cleared['comments']);

			 $db->query_insert($this->page->pg_pre.'posts',$post_cleared);
			 $counterPosts++;
			 if(isset($post['comments']))
			 {
				foreach($post['comments'] as $cid=>$comment)
				{
				  $comment_id=$db->query_insert($this->page->pg_pre.'comments',$comment);
				  $counterComments++;
				  if($comment['parent_id']>0)
						$comment['parent_id']=$post['comments'][$comment['parent_id']]['comment_id'];

				  if(isset($comment['author']) && isset($data['authors'][$comment['author']]))
						$comment['uid']=$data['authors'][$comment['author']]['uid'];
				  unset($comment['comment_id']);
				  if($comment['approved']==1)
						$this->page->commentModule->db_update_comment_count($entry_id);
				  $post['comments'][$cid]['comment_id']=$comment_id;
				}
			 }

			 $entry_id++;
		}

		unlink($file);
		echo json_encode('Import Ready. '.$counterAuthors.' authors,'.$counterCategories.' categories,'.$counterPosts.' posts, '.$counterComments.' comments imported' );
	}
}

class WXR_Parser_SimpleXML
{
	function parse( $file )
	{
		$authors = $posts = $categories = $tags = $terms = array();

		libxml_use_internal_errors(true);
		$xml = simplexml_load_file( $file );

		if ( ! $xml )
			return json_encode('There was an error when reading this WXR file'.libxml_get_errors());

		$wxr_version = $xml->xpath('/rss/channel/wp:wxr_version');
		if ( ! $wxr_version )
			return json_encode('This does not appear to be a WXR file, missing/invalid WXR version number');

		$wxr_version = (string) trim( $wxr_version[0] );
		if ( ! preg_match( '/^\d+\.\d+$/', $wxr_version ) )
			return json_encode('This does not appear to be a WXR file, missing/invalid WXR version number');

		$base_url = $xml->xpath('/rss/channel/wp:base_site_url');
		$base_url = (string) trim( $base_url[0] );

		$namespaces = $xml->getDocNamespaces();
		if ( ! isset( $namespaces['wp'] ) )
			$namespaces['wp'] = 'http://wordpress.org/export/1.1/';
		if ( ! isset( $namespaces['excerpt'] ) )
			$namespaces['excerpt'] = 'http://wordpress.org/export/1.1/excerpt/';

		foreach ( $xml->xpath('/rss/channel/wp:author') as $author_arr )
		{
			$a = $author_arr->children( $namespaces['wp'] );
			$login = (string) $a->author_login;
			$authors[$login] = array(
				'uid' => (int) $a->author_id,
				'username' => $login,
				'display_name' => (string) $a->author_display_name,
				'email' => (string) $a->author_email,
				'first_name' => (string) $a->author_first_name,
				'last_name' => (string) $a->author_last_name
			);
		}

		foreach ( $xml->xpath('/rss/channel/wp:category') as $term_arr )
		{
			$t = $term_arr->children( $namespaces['wp'] );
			$categories[] = array(
				'cname' => (string) $t->cat_name,
				'description' => (string) $t->category_description
			);
		}

		foreach ( $xml->xpath('/rss/channel/wp:tag') as $term_arr )
		{
			$t = $term_arr->children( $namespaces['wp'] );
			$tags[] = array(
				'term_id' => (int) $t->term_id,
				'tag_slug' => (string) $t->tag_slug,
				'tag_name' => (string) $t->tag_name,
				'tag_description' => (string) $t->tag_description
			);
		}

		// grab posts
		foreach ( $xml->channel->item as $item )
		{
			$post = array(
				'title' => (string) $item->title
			);

			$dc = $item->children( 'http://purl.org/dc/elements/1.1/' );
			$post['author'] = (string) $dc->creator;

			$content = $item->children( 'http://purl.org/rss/1.0/modules/content/' );
			$excerpt = $item->children( $namespaces['excerpt'] );
			$post['content'] = (string) $content->encoded;
			$post['excerpt'] = (string) $excerpt->encoded;

			$wp = $item->children( $namespaces['wp'] );
			$post['creation_date'] = (string) $wp->post_date;
			$post['allow_comments'] = ((string) $wp->comment_status=='open')?1:0;
			$post['allow_pings'] = ((string) $wp->ping_status=='open')?1:0;
			$post['publish_status'] = (string) $wp->status;
			$post['keywords']='';

			if ( isset($wp->attachment_url) )
				$post['mediafile_url'] = (string) $wp->attachment_url;

			foreach ( $item->category as $c )
			{
				$att = $c->attributes();
				if ( isset( $att['nicename'] ) )
				{
					$domain=(string) $att['domain'];
					if($domain=='category')
						 $post['category']=(string) $att['nicename'];
					elseif($domain=='post_tag')
						 $post['keywords'].=(string) $att['nicename'].',';
				}
			}

			foreach ( $wp->comment as $comment )
			{
				$comment_id=(int) $comment->comment_id;
				$post['comments'][$comment_id] = array(
					'comment_id' => $comment_id,
					'visitor' => (string) $comment->comment_author,
					'email' => (string) $comment->comment_author_email,
					'ip' => (string) $comment->comment_author_IP,
					'date' => (string) $comment->comment_date,
					'comments' => (string) $comment->comment_content,
					'approved' => (string) $comment->comment_approved,
					'parent_id' => (string) $comment->comment_parent,
					'uid' => (int) $comment->comment_user_id
				);
			}

			$posts[] = $post;
		}

		if(empty($categories))
		{
			 foreach($posts as $post)
			 {
				  $categories[$post['category']]=array(
	 	 			'cname' => $post['category'],
		  			'description' => ''
					 );
			 }
		}

		return array(
			'authors' => $authors,
			'posts' => $posts,
			'categories' => $categories,
			'tags' => $tags,
			'terms' => $terms,
			'base_url' => $base_url,
			'version' => $wxr_version
		);
	}
}

