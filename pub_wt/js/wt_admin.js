/***********************************************************************
 # *          @Project    : WT Store
 # *          @version    : 2.0
 # *          @author     : Mogbil Sourketti info[@]wondtech.com
 # *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 # *          @link       : http://www.wondtech.com
 # *          @package    : WT FrameWork (2.0)
 # ************************************************************************/

$(function (){
    $('.del').click(function () {
        var del = confirm('Delete - حذف');
        if(del==true) return true;
        else return false;
    });

    var i = 1;
    $('#addImg').click(function() {
        if(i !== 5) {
            var upImg = $('<label class="btn btn-outline-secondary btn-sm" for="p_img' + (i) + '"><i class="fa fa-image" aria-hidden="false"></i> Image - صورة</label><input type="file" id="p_img' + (i) + '" name="p_img' + (i) + '" style="display: none">');
            i++; $('#upImg').append(upImg);
        }
    });

    $('#inputGroupFile01').change(function(){
        var fileName = $(this).val().split('\\').pop();;
        $(this).next('.custom-file-label').html(fileName);
    });
});