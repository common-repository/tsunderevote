<?php

/**
 * tsundere vote
 * @Author: huchao
 * @Date:   2020-05-01 21:44:19
 * @Last Modified by:   huchao
 * @Last Modified time: 2020-05-01 22:32:38
 */
class TsundereVote
{
    public function __construct()
    {
        add_action('the_content', array($this, 'addVoteHTML'), 10, 1);

        add_action('wp_ajax_nopriv_imwp_addvote', array($this, 'addVote'));
        add_action('wp_ajax_imwp_addvote', array($this, 'addVote'));

        add_action('wp_ajax_nopriv_imwp_cancelvote', array($this, 'cancelVote'));
        add_action('wp_ajax_imwp_cancelvote', array($this, 'cancelVote'));

        add_action('wp_ajax_nopriv_imwp_getvote', array($this, 'getVote'));
        add_action('wp_ajax_imwp_getvote', array($this, 'getVote'));

        add_action('wp_enqueue_scripts', array($this, 'addScripts'));
    }

    /**
     * add scripts for this plugin
     * @return  null
     */
    public function addScripts()
    {
        wp_enqueue_style('tsundere_vote', plugin_dir_url(dirname(__FILE__)) .'assets/vote.css');
        wp_enqueue_script('tsundere_vote', plugin_dir_url(dirname(__FILE__)) .'assets/vote.js',array(), false, true);
        $script =  'var imwp_ajaxurl = "'.get_admin_url(null, '/admin-ajax.php') . '";';
        wp_add_inline_script('tsundere_vote', $script);
    }


    /**
     * get Vote
     * @return null
     */
    public function getVote()
    {
        $postId = (int) $_POST['post_id'];
        if (!$postId) {
            die('{"code":1, "msg":"empty post_id"}');
        }
        $data = $this->get($postId);
        $msg = array('code' => 0, 'data' => $data);
        die(json_encode($msg));
    }

    /**
     * add vote
     * @return  null
     */
    public function addVote()
    {
        $postId = (int) $_POST['post_id'];
        if (!$postId) {
            die('{"code":1, "msg":"empty post_id"}');
        }

        if (isset($_COOKIE['tsv_'.$postId]) && $_COOKIE['tsv_'.$postId] == "1") {
            die('{"code":1, "msg":"已经投过了"}');
        }

        $field = 'tsv_' . addslashes(trim($_POST['field']));
        $valid = array(
            'tsv_g'     => 1,
            'tsv_sg'    => 1,
            'tsv_vg'    => 1
        );
        
        if (!isset($valid[$field])) {
            die('{"code":1, "msg":"访问非法"}');
        }
        
        if ($this->add($postId, $field, 1)) {
            $msg = array(
                'code'  => 0,
                'msg'   => '成功'
            );
            setcookie("tsv_".$postId, $field, time()+86400, '/');
        } else {
            $msg = array(
                'code'  => 1,
                'msg'   => '失败',
            );
        }
        die(json_encode($msg));
    }


    /**
     * cancel vote
     * @return  null
     */
    public function cancelVote()
    {
        $postId = (int) $_POST['post_id'];

        if (!$postId) {
            die('{"code":1, "msg":"empty post_id"}');
        }

        if (!isset($_COOKIE['tsv_'.$postId])) {
            die('{"code":1, "msg":"还未投票"}');
        }

        $field = 'tsv_' . addslashes(trim($_POST['field']));
        $valid = array(
            'tsv_g'     => 1,
            'tsv_sg'    => 1,
            'tsv_vg'    => 1,
        );
        
        if (!isset($valid[$field])) {
            die('{"code":1, "msg":"访问非法"}');
        }
        
        if ($this->add($postId, $field, -1)) {
            $msg = array(
                'code' => 0,
                'msg'  => '成功',
            );
            setcookie("tsv_".$postId, $field, time() , '/');
        } else {
            $msg = array(
                'code' => 1,
                'msg'  => '失败',
            );
        }
        die(json_encode($msg));

    }

    /**
     * get all vote data for the post
     * @param  int $postId
     * @return  array
     */
    public function get($postId)
    {
        $data = array();
        $data['tsv_g'] = get_post_meta($postId, 'tsv_g', true);
        $data['tsv_sg'] = get_post_meta($postId, 'tsv_sg', true);
        $data['tsv_vg'] = get_post_meta($postId, 'tsv_vg', true);
        return $data;
    }

    /**
     * add vote data for the post
     * @param  int $postId
     * @param  string $field
     * @param  int $num
     */
    public function add($postId, $field, $num = 1)
    {
        if (!add_post_meta($postId, $field, 1, true)) {
            $old = get_post_meta($postId, $field, true);
            return update_post_meta($postId, $field, $old+$num, $old);
        }
        return true;
    }

    /**
     * add tips to post content
     * use ajax load vote data
     * 
     * @param  string $content post content
     * @return  content with vote
     */
    public function addVoteHTML($content)
    {
        if (!is_singular()) {
            return $content;
        }
        $id = get_the_ID();
        $vote = "
        <div id=\"tsv_action\" data-post_id=".$id.">
            <span id=\"tsv_g\" class=\"tsv_action\" data-action=\"g\" data-post_id=".$id.">好看 (<span id=\"tsv_g_num\">0</span>)</span>
            <span id=\"tsv_sg\" class=\"tsv_action\" data-action=\"sg\" data-post_id=".$id.">很好看 (<span id=\"tsv_sg_num\">0</span>)</span>
            <span id=\"tsv_vg\" class=\"tsv_action\" data-action=\"vg\" data-post_id=".$id.">非常好看 (<span id=\"tsv_vg_num\">0</span>)</span>
        </div>";
        return $content . $vote;
    }

}

new TsundereVote;