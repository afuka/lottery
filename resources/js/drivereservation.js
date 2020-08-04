$(function() {
    var pageswiper = new Swiper('.main-page', {
        direction: 'vertical',
    })
    var swiperScrollbar = new Swiper('.page2-info', {
        scrollbar: '.page2-info .swiper-scrollbar',
        direction: 'vertical',
        slidesPerView: 'auto',
        mousewheelControl: true,
        freeMode: true,
        nested: true
    });
    var infoswiper = new Swiper('.page-info', {
        direction: 'horizontal',
        loop: true,
        navigation: {
            nextEl: '.page-info .swiper-button-next',
            prevEl: '.page-info .swiper-button-prev',
        },
    });
    var dealerData = JSonData['Information'];
    function unique(arr) {
        if (!Array.isArray(arr)) {
            console.log('type error!');
            return;
        }
        var array = [];
        for (var i = 0; i < arr.length; i++) {
            if (array .indexOf(arr[i]) === -1) {
                array .push(arr[i]);
            }
        }
        return array;
    }
    function getProvince(){
        var province = [];
        for (var i = 0; i < dealerData.length; i++) {
            province.push(dealerData[i].pro);
        }
        return unique(province);
    }
    function initProvince() {
        var province = getProvince();
        var str = '<option value="" selected>请选择城市</option>';
        for (var i = 0; i < province.length; i++) {
            str += '<option value="' + province[i] + '">' + province[i] + '</option>';
        }
        $('.province').html(str);
    }
    function resetProvince(){
        initProvince();
        $('.province').prev().text('请选择城市');
        resetCity('');
    }
    initProvince();
    $('.province').on('change', function() {
        var val = $(this).find('option:selected').val();
        var name = $(this).find('option:selected').text();
        $(this).prev().text(name);
        resetCity(val);
    })
    function resetCity(province){
        var str = '<option value="" selected>请选择地区</option>';
        var arr = [];
        for (var j = 0; j < dealerData.length; j++) {
            if (dealerData[j].pro === province) {
                arr.push(dealerData[j].city);
            }
        }
        arr = unique(arr)
        for (var i = 0; i < arr.length; i++) {
            str += '<option value="' + arr[i] + '">' + arr[i] + '</option>';
        }
        $('.city').html(str);
        // reset
        $('.city').prev().text('请选择地区');
        $('.dealer').html('<option value="" selected>请选择经销商</option>').prev().text('请选择经销商');
    }
    $('.city').on('change', function() {
        var val = $(this).find('option:selected').val();
        var name = $(this).find('option:selected').text();
        $(this).prev().text(name);
        resetDealer(val);
    })
    function resetDealer(city){
        var str = '<option value="" selected>请选择经销商</option>';
        for (var i = 0; i < dealerData.length; i++) {
            if (dealerData[i].city === city) {
                str += '<option value="' + dealerData[i].code + '" data-full="' + dealerData[i].dealerfull + '">' + dealerData[i].dealer + '</option>';
            }
        }
        $('.dealer').html(str);
        // reset
        $('.dealer').prev().text('请选择经销商');
    }
    $('.dealer').on('change', function() {
        var val = $(this).find('option:selected').val();
        var name = $(this).find('option:selected').text();
        $(this).prev().text(name);
    })
    function getParam(n) {
        var urlVal = location.href.split('?')[1];
        if (typeof urlVal === 'undefined') return null;
        var query = [];
        if (urlVal.includes('&')) {
            query = urlVal.split('&');
        } else {
            query.push(urlVal)
        }
        for (var i = 0; i < query.length; i++) {
            var kv = query[i].split('=');
            if (kv[0] === n) {
                return kv[1].replace(/[\#|\/|\$].*/g, '')
            }
        }
        return null
    };
    var media = getParam('media');
    function showToast(text){
        $('.show-toast').fadeIn().find('span').text(text);
        setTimeout(function() {
            $('.show-toast').fadeOut(function(){
                $('.show-toast span').text('');
            });
        }, 2000);
    }
    var submitFlag = true;
    $('.page2-submit').on('click', function(){
        if (!submitFlag) return false;
        var province = $('.province').find('option:selected').val();
        if (province === '') {
            showToast('请选择城市');
            return false;
        }
        var city = $('.city').find('option:selected').val();
        if (city === '') {
            showToast('请选择地区');
            return false;
        }
        var dealer_val = $('.dealer').find('option:selected').val();
        var dealer_text = $('.dealer').find('option:selected').attr('data-full');
        if (dealer_val === '') {
            showToast('请选择经销商');
            return false;
        }
        var name = $('.name').val();
        if (name === '') {
            showToast('请填写姓名');
            return false;
        }
        var tel = $('.tel').val();
        if (tel === '') {
            showToast('请填写电话号码');
            return false;
        }
        if (!/^1(3|4|5|6|7|8|9)\d{9}$/.test(tel)) {
            showToast('请填写正确的电话号码');
            return false;
        }
        submitFlag = false;
        $.post('https://ix25-api.ritsc.com/XdMode/savedriveCrm/project/1033', {
            'da[name]': name,
            'da[mobile]': tel,
            'da[province]': province,
            'da[city]': city,
            'da[dealer]': dealer_text + '|' + dealer_val,
            'da[media]': media,
            'da[client]': MClient
        }, function(res){
            if (res.status == 1) {
                showToast('提交成功');
                resetProvince();
                $('.name').val('');
                $('.tel').val('');
            } else {
                showToast(res.info);
            }
            submitFlag = true;
        }, 'jsonp')
    })
})