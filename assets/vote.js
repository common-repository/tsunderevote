jQuery(document).ready(function($){

    var tsv = {

        init:function() {
            var self = this;
            this.id = $("#tsv_action").data('post_id');
            $.ajax({
                "url": imwp_ajaxurl + "?action=imwp_getvote",
                "type":"POST",
                "dataType":"json",
                "data":{
                    "tsv": 1,
                    "post_id": this.id,
                },
                success:function(data){
                    $("#tsv_g_num").html(data['data']['tsv_g'] || 0);
                    $("#tsv_sg_num").html(data['data']['tsv_sg'] || 0);
                    $("#tsv_vg_num").html(data['data']['tsv_vg'] || 0);
                    var currentVote = self.cookie('tsv_' + self.id);
                    if (currentVote) {
                        $('#'+currentVote).addClass('tsv_voted');
                    }
                },
                error:function(){
                }
            });

            $(".tsv_action").click(function(){
                var action = $(this).data('action');
                var voteNumDom = $("#tsv_" + action + '_num');

                if ($(this).hasClass('tsv_voted')) {
                    voteNumDom.html(parseInt(voteNumDom.html())-1);
                    self.cancel(action, self.id);
                    $(this).removeClass('tsv_voted');
                    return ;
                }

                if (self.cookie('tsv_'+self.id)) {
                    alert("已经点过啦");
                    return ;
                }

                voteNumDom.html(parseInt(voteNumDom.html())+1);
                $(this).addClass('tsv_voted');
                self.add(action, self.id);
                
            });

        },

        change:function(vtype, postId, action)
        {
            if (action == 'add') {
                var requestAction = 'imwp_addvote';
            } else {
                var requestAction = 'imwp_cancelvote';
            }

            $.ajax({
                "url": imwp_ajaxurl + "?action=" + requestAction,
                "type":"POST",
                "dataType":"json",
                "data":{
                    "tsv": 1,
                    "field": vtype,
                    "post_id": postId,
                },
                success:function(data){
                    if (data['code'] == 1) {
                        //alert
                    } else {

                    }
                },
                error:function(){
                    alert("系统错误");
                }
            });
        },

        add:function(vtype, postId)
        {
            this.change(vtype, postId, 'add');
        },

        cancel:function(vtype, postId)
        {
            this.change(vtype, postId, 'cancel');
        },

        cookie:function(key, value, expire)
        {
            if (!value) {
                return this.getcookie(key);
            } else {
                return this.setcookie(key, value, expire);
            }
        },

        getcookie:function(key)
        {
            var arr,reg = new RegExp("(^| )"+key+"=([^;]*)(;|$)");
            if(arr = document.cookie.match(reg)) {
                return unescape(arr[2]);
            } else {
                return null;
            }
        },

        setcookie:function(key, value, expire)
        {
            var exp = new Date();
            exp.setTime(exp.getTime() + expire*1000);
            document.cookie = key + "="+ escape(value) + ";expires=" + exp.toGMTString();
        }
    }

    tsv.init();
});