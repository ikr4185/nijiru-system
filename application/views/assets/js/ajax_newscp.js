// DOMを全て読み込んだあとに実行される
$(function () {
    // 「#execute」をクリックしたとき
    $('#execute').click(function () {
        $.ajax({
            url: '/class/ajax/ajax_newscp.php',
            type: 'post',
            dataType: 'jsonp',
            jsonpCallback: 'callback',
            data: {
                age: $('#age').val(),
                job: $('#job').val()
            }
        }).done(function(response){
            $('#result').val('成功');
            $('#detail').val(response.data);
        }).fail(function(){
            $('#result').val('失敗');
            $('#detail').val('');
        });
    });
});