
function myAjax(uri, data, success, error) {

    $.ajax({
        url: uri,
        type: "POST",
        data: data,
        // dataType: "text",
        error: error,
        success: success
    })
}

function popupSubscrib() {

    $popup = $('#subscribe');
    $btn = $('#subscr_btn');

    $popup.show(2000);

    $btn.on('click', function(){
        $popup.hide(1000);
    });

}


$(document).ready(function () {
    $online = $('#online');
    $visited = $('#visited');

    time_id = setInterval(function () {
        rand = Math.ceil(Math.random() * 5) ;
        $online.html(rand);
        visited = rand + parseInt($visited.html());
        $visited.html(visited);
    },3000);

    time_subscrib = setTimeout(function () {
        popupSubscrib();
    }, 15000);

    slider();

    //
    // $(window).unload(function(){
    //     alert("Пока, пользователь!");
    // });

})




function closeIt()
{
    alert('oooooo');
    return "Пожалуйста, не закрывайте меня!";
}
window.onbeforeunload = closeIt;




