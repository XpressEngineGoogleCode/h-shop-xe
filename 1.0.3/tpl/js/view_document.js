/*************************
* 썸네일 이미지처리
************************/

function doNextThumb()
{
	var thumb_max = jQuery("img.prod_thumb").size() - 1;
	var tmp = jQuery("img.prod_thumb");
	var thumb_tmp1 = jQuery(tmp[0]).attr("src");
	var thumb_tmp2 = jQuery(tmp[0]).attr("tabindex");
	var thumb_tmp3 = jQuery(tmp[0]).attr("image_middle");
	
	jQuery("img.prod_thumb").each(function(i){
		if( i < thumb_max ){
			jQuery(tmp[i]).attr("src", jQuery(tmp[i+1]).attr("src") );
			jQuery(tmp[i]).attr("tabindex", jQuery(tmp[i+1]).attr("tabindex") );
			jQuery(tmp[i]).attr("image_middle", jQuery(tmp[i+1]).attr("image_middle") );
		}else{
			jQuery(tmp[i]).attr("src", thumb_tmp1 );
			jQuery(tmp[i]).attr("tabindex", thumb_tmp2 );
			jQuery(tmp[i]).attr("image_middle", thumb_tmp3 );
		}
	})
}

function doPrevThumb()
{
	var thumb_max = jQuery("img.prod_thumb").size() - 1;
	var tmp = jQuery("img.prod_thumb");
	var thumb_tmp1 = jQuery(tmp[thumb_max]).attr("src");
	var thumb_tmp2 = jQuery(tmp[thumb_max]).attr("tabindex");
	var thumb_tmp3 = jQuery(tmp[thumb_max]).attr("image_middle");
	
	jQuery("img.prod_thumb").each(function(ii){
		i = thumb_max - ii;
		if( i > 0 ){
			jQuery(tmp[i]).attr("src", jQuery(tmp[i-1]).attr("src") );
			jQuery(tmp[i]).attr("tabindex", jQuery(tmp[i-1]).attr("tabindex") );
			jQuery(tmp[i]).attr("image_middle", jQuery(tmp[i-1]).attr("image_middle") );
		}else{
			jQuery(tmp[i]).attr("src", thumb_tmp1 );
			jQuery(tmp[i]).attr("tabindex", thumb_tmp2 );
			jQuery(tmp[i]).attr("image_middle", thumb_tmp3 );
		}
	})
}

