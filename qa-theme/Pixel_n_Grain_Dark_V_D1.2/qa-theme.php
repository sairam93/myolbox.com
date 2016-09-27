<?php
/*
*	Q2A Market Pixel n Grain Dark
*
*	Theme file
*	File: qa-theme.php
*	
*	@author			Q2A Market
*	@category		Plugin
*	@Version: 		D1.2
*   @author URL:	http://www.q2amarket.com
*	
*	@Q2A Version	1.5.4
*
*/

	class qa_html_theme extends qa_html_theme_base
	{		
		// adding ie specific css
		function head_script()
		{
			if (isset($this->content['script']))
				foreach ($this->content['script'] as $scriptline)
					$this->output_raw($scriptline);
			
			$this->output('<!--[if IE]>');	
			$this->output('<LINK REL="stylesheet" TYPE="text/css" HREF="'.$this->rooturl.$this->ie_css().'"/>');
			$this->output('<![endif]-->');
		}
		
		function ie_css()
		{
			return 'ie.css';		
		}
		
		
		// header part
		function nav_user_search() // reverse the usual order
		{
			$this->search();
			$this->nav('user');
		}
		
		// main content div
		function q_view_content($q_view)
		{
			if (!empty($q_view['content']))
				$this->output(
					'<DIV CLASS="qa-q-view-content clearfix">',
					$q_view['content'],
					'</DIV>'
				);
		}

		// hide sidebar on user profile page
		function sidepanel()
		{
			if($this->template != 'user')
				qa_html_theme_base::sidepanel();
		}
		
		// custom footer
		function footer()
        {
            $this->output('<DIV CLASS="qa-footer">');			
			
			$this->output('<DIV CLASS="footer-copyright">');
			$this->output('<p>Copyright &copy; '.date('Y').' '.$this->content['site_title'].' - All rights reserved.</p>');
			$this->output('</DIV>');
			
			$this->attribution();							
			
			$this->output('<DIV CLASS="footer-credit">');
			$this->output('<p>Theme Designed By: <a href="http://www.q2amarket.com">Q2A Market</a></p>');
			$this->output('</DIV>');
			
			$this->nav('footer');	
			
			$this->footer_clear();
            
            $this->output('</DIV> <!-- END qa-footer -->', '');
			

        }
	}
	
	
	 

/*
	Omit PHP closing tag to help avoid accidental output
*/