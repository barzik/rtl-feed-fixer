<?php

/**
 * RTL feed fixer test suite
 */
class WP_Test_WPnotice_Plugin_Tests extends WP_UnitTestCase {


    /**
     * setup
     *
     */

    function setUp() {
        parent::setUp();

        update_option( 'rss_use_excerpt', true);
        update_option( 'posts_per_rss', 10);

        $this->post_count = get_option('posts_per_rss');
        $this->excerpt_only = get_option('rss_use_excerpt');

        $this->dummy_text_ltr = '<p>Lorem ipsum <i>text</i> will be shown here <i>this</i> is the first paragraph</p>';
        $this->dummy_text_rtl = '<p dir="rtl" style="direction: rtl; text-align: right">Lorem ipsum <i>text</i> will be shown here <i>this</i> is the first paragraph</p>';

        // this seems to break something
        update_option('use_smilies', false);
    }

	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function test_plugin_activated() {
		$this->assertTrue( is_plugin_active( 'rtl-feed-fixer/rtl-feed-fixer.php' ) );
	}

    /**
     * function conversion - ltr to rtl
     */

    function test_conversion_function() {
        $plugin = RtlFeedFixer::get_instance();

        $dummy_text = $this->dummy_text_ltr;
        $expected = $this->dummy_text_rtl;

        $actual = $plugin->add_rtl_to_p( $dummy_text );
        $this->assertEquals( $expected, $actual );

    }


    /**
     * test that rss functionality is not broken
     * @throws Exception
     */

    function test_rss() {
        $this->go_to('/feed/');
        $feed = $this->do_rss2();
        $xml = xml_to_array($feed);

        // get the rss element
        $rss = xml_find($xml, 'rss');

        // there should only be one rss element
        $this->assertEquals(1, count($rss));

        $this->assertEquals('2.0', $rss[0]['attributes']['version']);
        $this->assertEquals('http://purl.org/rss/1.0/modules/content/', $rss[0]['attributes']['xmlns:content']);
        $this->assertEquals('http://wellformedweb.org/CommentAPI/', $rss[0]['attributes']['xmlns:wfw']);
        $this->assertEquals('http://purl.org/dc/elements/1.1/', $rss[0]['attributes']['xmlns:dc']);

        // rss should have exactly one child element (channel)
        $this->assertEquals(1, count($rss[0]['child']));
    }

    /**
     *
     * the feed are being transferred to RTL
     *
     * @throws Exception
     */

    function test_feed_conversion() {

        $user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
        $args = array(
            'post_title' => 'Draft #1',
            'post_author' => $user_id,
            'post_content' => $this->dummy_text_ltr,
            'post_excerpt' => $this->dummy_text_ltr
        );
        $this->factory->post->create_many( $this->post_count , $args );

        $this->go_to('/feed/');
        $feed = $this->do_rss2();
        $xml = xml_to_array($feed);

        // get all the rss -> channel -> item elements
        $items = xml_find($xml, 'rss', 'channel', 'item');


        for  ($i=0; $i < $this->post_count; $i++ ) {

            // description/excerpt
            $description = xml_find($items[$i]['child'], 'description');
            $this->assertEquals($this->dummy_text_rtl, trim($description[0]['content']));
            // post content
            if (!$this->excerpt_only) {
                $content = xml_find($items[$i]['child'], 'content:encoded');
                $this->assertEquals($this->dummy_text_rtl, trim($content[0]['content']));
            }
        }
    }

    /**
     *
     * aux function
     *
     * @return string
     * @throws Exception
     */

    function do_rss2() {
        ob_start();
        // nasty hack
        global $post;
        try {
            @require(ABSPATH . 'wp-includes/feed-rss2.php');
            $out = ob_get_clean();
        } catch (Exception $e) {
            $out = ob_get_clean();
            throw($e);
        }
        return $out;
    }




}
