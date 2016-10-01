<?php

/*
*   Q2AM Edit and Moderate
*   
*   override function to allow edit questions
* 	in moderation mode
*   
*   @author         Q2A Market
*   @category       Plugin
*   @Version        1.00
*   @URL            http://www.q2amarket.com
*   
*   @Q2A Version    1.6+
*
*   Do not modify this file unless you know what
*   you are doing. Any modifcation can directly
*   affect to the Q2A system and may or may not be
*   recovered.
*/

	function qa_page_q_post_rules($post, $parentpost=null, $siblingposts=null, $childposts=null)

	{
				
		$userid=qa_get_logged_in_userid();
		$cookieid=qa_cookie_get();
		$userlevel=qa_user_level_for_post($post);
		
		$rules['isbyuser']=qa_post_is_by_user($post, $userid, $cookieid);
		$rules['queued']=(substr($post['type'], 1)=='_QUEUED');
		$rules['closed']=($post['basetype']=='Q') && (isset($post['closedbyid']) || (isset($post['selchildid']) && qa_opt('do_close_on_select')));

	//	Cache some responses to the user permission checks

		$permiterror_post_q=qa_user_permit_error('permit_post_q', null, $userlevel); // don't check limits here, so we can show error message
		$permiterror_post_a=qa_user_permit_error('permit_post_a', null, $userlevel);
		$permiterror_post_c=qa_user_permit_error('permit_post_c', null, $userlevel);

		$permiterror_edit=qa_user_permit_error(($post['basetype']=='Q') ? 'permit_edit_q' :
			(($post['basetype']=='A') ? 'permit_edit_a' : 'permit_edit_c'), null, $userlevel);
		$permiterror_retagcat=qa_user_permit_error('permit_retag_cat', null, $userlevel);
		$permiterror_flag=qa_user_permit_error('permit_flag', null, $userlevel);
		$permiterror_hide_show=qa_user_permit_error($rules['isbyuser'] ? null : 'permit_hide_show', null, $userlevel);
		$permiterror_close_open=qa_user_permit_error($rules['isbyuser'] ? null : 'permit_close_q', null, $userlevel);
		$permiterror_moderate=qa_user_permit_error('permit_moderate', null, $userlevel);

	//	General permissions

		$rules['authorlast']=((!isset($post['lastuserid'])) || ($post['lastuserid']===$post['userid']));
		$rules['viewable']=$post['hidden'] ? (!$permiterror_hide_show) : ($rules['queued'] ? ($rules['isbyuser'] || !$permiterror_moderate) : true);
		
	//	Answer, comment and edit might show the button even if the user still needs to do something (e.g. log in)
		
		$rules['answerbutton']=($post['type']=='Q') && ($permiterror_post_a!='level') && (!$rules['closed']) &&
			(qa_opt('allow_self_answer') || !$rules['isbyuser']);

		$rules['commentbutton']=(($post['type']=='Q') || ($post['type']=='A')) &&
			($permiterror_post_c!='level') && qa_opt(($post['type']=='Q') ? 'comment_on_qs' : 'comment_on_as');
		$rules['commentable']=$rules['commentbutton'] && !$permiterror_post_c;

		$rules['editbutton']=(!$post['hidden']) && (!$rules['closed']) && 
			($rules['isbyuser'] || (($permiterror_edit!='level') && ($permiterror_edit!='approve')));
		$rules['editable']=$rules['editbutton'] && ($rules['isbyuser'] || !$permiterror_edit);
		
		$rules['retagcatbutton']=($post['basetype']=='Q') && (qa_using_tags() || qa_using_categories()) && 
			(!$post['hidden']) && ($rules['isbyuser'] || (($permiterror_retagcat!='level') && ($permiterror_retagcat!='approve')) );
		$rules['retagcatable']=$rules['retagcatbutton'] && ($rules['isbyuser'] || !$permiterror_retagcat);
		
		if ($rules['editbutton'] && $rules['retagcatbutton']) { // only show one button since they lead to the same form
			if ($rules['retagcatable'] && !$rules['editable'])
				$rules['editbutton']=false; // if we can do this without getting an error, show that as the title
			else
				$rules['retagcatbutton']=false;
		}
		
		$rules['aselectable']=($post['type']=='Q') && !qa_user_permit_error($rules['isbyuser'] ? null : 'permit_select_a', null, $userlevel);

		$rules['flagbutton']=qa_opt('flagging_of_posts') && (!$rules['isbyuser']) && (!$post['hidden']) && (!$rules['queued']) &&
			(!@$post['userflag']) && ($permiterror_flag!='level') && ($permiterror_flag!='approve');
		$rules['flagtohide']=$rules['flagbutton'] && (!$permiterror_flag) && (($post['flagcount']+1)>=qa_opt('flagging_hide_after'));
		$rules['unflaggable']=@$post['userflag'] && (!$post['hidden']);
		$rules['clearflaggable']=($post['flagcount']>=(@$post['userflag'] ? 2 : 1)) && !qa_user_permit_error('permit_hide_show', null, $userlevel);
		
	//	Other actions only show the button if it's immediately possible
		
		$notclosedbyother=!($rules['closed'] && isset($post['closedbyid']) && !$rules['authorlast']);
		$nothiddenbyother=!($post['hidden'] && !$rules['authorlast']);
		
		$rules['closeable']=qa_opt('allow_close_questions') && ($post['type']=='Q') && (!$rules['closed']) && !$permiterror_close_open;
		$rules['reopenable']=$rules['closed'] && isset($post['closedbyid']) && (!$permiterror_close_open) && (!$post['hidden']) &&
			($notclosedbyother || !qa_user_permit_error('permit_close_q', null, $userlevel));
			// cannot reopen a question if it's been hidden, or if it was closed by someone else and you don't have global closing permissions
		$rules['moderatable']=$rules['queued'] && !$permiterror_moderate;
		$rules['hideable']=(!$post['hidden']) && ($rules['isbyuser'] || !$rules['queued']) &&
			(!$permiterror_hide_show) && ($notclosedbyother || !qa_user_permit_error('permit_hide_show', null, $userlevel));
			// cannot hide a question if it was closed by someone else and you don't have global hiding permissions
		$rules['reshowimmed']=$post['hidden'] && !qa_user_permit_error('permit_hide_show', null, $userlevel);
			// means post can be reshown immediately without checking whether it needs moderation
		$rules['reshowable']=$post['hidden'] && (!$permiterror_hide_show) &&
			($rules['reshowimmed'] || ($nothiddenbyother && !$post['flagcount']));
			// cannot reshow a question if it was hidden by someone else, or if it has flags - unless you have global hide/show permissions
		$rules['deleteable']=$post['hidden'] && !qa_user_permit_error('permit_delete_hidden', null, $userlevel);
		$rules['claimable']=(!isset($post['userid'])) && isset($userid) && strlen(@$post['cookieid']) && (strcmp(@$post['cookieid'], $cookieid)==0) &&
			!(($post['basetype']=='Q') ? $permiterror_post_q : (($post['basetype']=='A') ? $permiterror_post_a : $permiterror_post_c));
		$rules['followable']=($post['type']=='A') ? qa_opt('follow_on_as') : false;
		
	//	Check for claims that could break rules about self answering and multiple answers

		if ($rules['claimable'] && ($post['basetype']=='A')) {		
			if ( (!qa_opt('allow_self_answer')) && isset($parentpost) && qa_post_is_by_user($parentpost, $userid, $cookieid) )
				$rules['claimable']=false;
			
			if (isset($siblingposts) && !qa_opt('allow_multi_answers'))
				foreach ($siblingposts as $siblingpost)
					if ( ($siblingpost['parentid']==$post['parentid']) && ($siblingpost['basetype']=='A') && qa_post_is_by_user($siblingpost, $userid, $cookieid))
						$rules['claimable']=false;
		}
		
	//	Now make any changes based on the child posts

		if (isset($childposts))
			foreach ($childposts as $childpost)
				if ($childpost['parentid']==$post['postid']) {
					$rules['deleteable']=false;
					
					if (($childpost['basetype']=='A') && qa_post_is_by_user($childpost, $userid, $cookieid)) {
						if (!qa_opt('allow_multi_answers'))
							$rules['answerbutton']=false;
						
						if (!qa_opt('allow_self_answer'))
							$rules['claimable']=false;
					}
				}
			
	//	Return the resulting rules

		return $rules;
	}

/*
	Omit PHP closing tag to help avoid accidental output
*/