/**
 * 將日期轉換成民國年.月.日的字串
 * @param {date} d 日期
 * @returns {String} 民國年.月.日
 */
var formatROCDate = function(d) {
    d = new Date(d);
    d.setFullYear(d.getFullYear() - 1911);
    var mm = d.getMonth() + 1,
            dd = d.getDate();
    if (mm < 10) {
        mm = '0' + mm;
    }
    if (dd < 10) {
        dd = '0' + dd;
    }
    return (d.getFullYear()) + '.' + mm + '.' + dd;
};
var scope;
$(document).ready(function() {
    $('.controlPanel #submit').click();

    //更新nav按鈕的active class
    function updateNavMenuActive(url) {
        $('.navbar-nav').find('li').removeClass('active');
        $('.navbar-nav').find('a[href="' + url + '"]').parent().addClass('active');
    }

    //把nav選單ajax化
    $('.navbar-nav a').click(function(e) {
        //載入新的網頁
        url = $(this).attr("href");
        $.get(url + '?ajax=1', function(data) {
            $("#mainContent").html(data);
            $('.controlPanel #submit').click();
        });
        //儲存history
        window.history.pushState(url, 'New Title', url);
        //更新active
        updateNavMenuActive(url);
        //取消原本的事件
        e.preventDefault();
    });

    //視窗按下上一頁的事件
    window.addEventListener('popstate', function(e) {
        if (history.state) {
            updateNavMenuActive(e.state);
        }
    }, false);
    scope = angular.element('.controlPanel').scope();
});
