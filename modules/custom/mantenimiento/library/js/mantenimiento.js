(function ($) {
    function onChannel(){
        console.log("allac");
        /* $("[id|=myonoffswitch]").on('click', function(){
            var ch = $(this).parent().parent().parent();
            $.ajax({
                type: 'POST',
                crossDomain: true,
                dataType: "json",
                // make sure you respect the same origin policy with this url:
                // http://en.wikipedia.org/wiki/Same_origin_policy
                url: 'http://18.206.191.173:3000/api/on',
                data: { 
                    'region': ch[0].children[0].innerText, 
                    'id': ch[0].children[1].innerText,
                    'name': ch[0].children[2].innerText,
                    'country': ch[0].classList[0]
                },
                success: function(msg){
                    alert('wow' + msg);
                }
            });
        }); */
    }
    function offChannel(){
        $("[id|=myonoffswitch]").on('click', function(){
            var chIn = $(this);
            var ch = $(this).parent().parent().parent();
            if(chIn.hasClass('off')){
                $.ajax({
                    type: 'POST',
                    crossDomain: true,
                    dataType: "json",
                    url: 'http://18.206.191.173:3000/api/off',
                    data: { 
                        'region': ch[0].children[0].innerText, 
                        'id': ch[0].children[1].innerText,
                        'name': ch[0].children[2].innerText,
                        'country': ch[0].classList[0]
                    },
                    success: function(msg){
                        window.location.reload();
                    }
                });
            }else{
                $.ajax({
                    type: 'POST',
                    crossDomain: true,
                    dataType: "json",
                    // make sure you respect the same origin policy with this url:
                    // http://en.wikipedia.org/wiki/Same_origin_policy
                    url: 'http://18.206.191.173:3000/api/on',
                    data: { 
                        'region': ch[0].children[0].innerText, 
                        'id': ch[0].children[1].innerText,
                        'name': ch[0].children[2].innerText,
                        'country': ch[0].classList[0]
                    },
                    success: function(msg){
                        window.location.reload();
                    }
                });
            }
            
            /* $.ajax({
                type: 'POST',
                crossDomain: true,
                dataType: "json",
                // make sure you respect the same origin policy with this url:
                // http://en.wikipedia.org/wiki/Same_origin_policy
                url: 'http://18.206.191.173:3000/api/on',
                data: { 
                    'region': ch[0].children[0].innerText, 
                    'id': ch[0].children[1].innerText,
                    'name': ch[0].children[2].innerText,
                    'country': ch[0].classList[0]
                },
                success: function(msg){
                    alert('wow' + msg);
                }
            }); */
        });
    }
    $(document).ready(function () {
        onChannel(),
        offChannel();
    });
})(jQuery);
