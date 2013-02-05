(function($){

var xScreen = null;

// 슬라이드를 위한 블랙 스크린을 만들거나 반환하는 함수
function getScreen() {
	var body    = $(document.body);
	var controls, imgframe, closebtn, prevbtn, nextbtn;

	// 스크린이 없으면 스크린을 만든다.
	if (!xScreen) {
		// 검은 스크린
		xScreen = $("<div>")
			.attr("id","xe_gallery_screen")
			.css({
				position:"absolute",
				display:"none",
				backgroundColor:"black",
				zIndex:500,
				opacity:0.5
			});

		// 이미지를 보여주고 컨트롤 버튼을 다룰 레이어
		controls = $("<div>")
			.attr("id","xe_gallery_controls")
			.css({
				position:"absolute",
				display:"none",
				overflow:"hidden",
				zIndex:510
			});

		// 닫기 버튼
		closebtn = $("<img>")
			.attr("id", "xe_gallery_closebtn")
			.attr("src", request_uri+"addons/resize_image/iconClose.png")
			.css({
				top : "10px"
			})
			.click(function(){xScreen.xeHide()})
			.appendTo(controls);

		// 이전 버튼
		prevbtn = $("<img>")
			.attr("id", "xe_gallery_prevbtn")
			.attr("src", request_uri+"addons/resize_image/iconLeft.png")
			.css("left","10px")
			.click(function(){xScreen.xePrev()})
			.appendTo(controls);

		// 다음 버튼
		nextbtn = $("<img>")
			.attr("id", "xe_gallery_nextbtn")
			.attr("src", request_uri+"addons/resize_image/iconRight.png")
			.css("right","10px")
			.click(function(){xScreen.xeNext()})
			.appendTo(controls);

		// 버튼 공통 속성
		controls.find("img")
			.attr({
				width  : 60,
				height : 60,
				className : "iePngFix"
			})
			.css({
				position : "absolute",
				width : "60px",
				height : "60px",
				zIndex : 530,
				cursor : "pointer"
			});

		// 이미지 홀더
		imgframe = $("<img>")
			.attr("id", "xe_gallery_holder")
			.css("border", "7px solid white")
			.css("zIndex", 520)
			.appendTo(controls).draggable();

		body.append(xScreen).append(controls);

		// xScreen 객체를 확장한다.
		xScreen.xeShow = function() {
			var clientWidth  = $(window).width();
			var clientHeight = $(window).height();

			$("#xe_gallery_controls,#xe_gallery_screen").css({
				display:"block",
				width  : clientWidth + "px",
				height : clientHeight + "px",
				left   : $(document).scrollLeft(),
				top    : $(document).scrollTop()
			});

			closebtn.css("left", Math.round((clientWidth-60)/2) + "px");

			$("#xe_gallery_prevbtn,#xe_gallery_nextbtn").css("top", Math.round( (clientHeight-60)/2 ) + "px");

			this.xeMove(0);
		};
		xScreen.xeHide = function(event) {
			xScreen.css("display","none");
			controls.css("display","none");
		};
		xScreen.xePrev = function() {
			this.xeMove(-1);
		};
		xScreen.xeNext = function() {
			this.xeMove(1);
		};
		xScreen.xeMove = function(val) {
			var clientWidth  = $(window).width();
			var clientHeight = $(window).height();

			this.index += val;

			prevbtn.css("visibility", (this.index>0)?"visible":"hidden");
			nextbtn.css("visibility", (this.index<this.list.size()-1)?"visible":"hidden");

			imgframe.attr("src", this.list.eq(this.index).attr("src"))
				.css({
					left : Math.round( Math.max( (clientWidth-imgframe.width()-14)/2, 0 ) ) + "px",
					top  : Math.round( Math.max( (clientHeight-imgframe.height()-14)/2, 0 ) ) + "px"
				});
		};

		// 스크린을 닫는 상황
		$(document).scroll(xScreen.xeHide);
		$(document).keydown(xScreen.xeHide);
		$(window).resize(xScreen.xeHide);
		$(window).scroll(xScreen.xeHide);
	} else {
		controls = $("#xe_gallery_controls");
		imgframe = $("#xe_gallery_holder");
		closebtn = $("#xe_gallery_closebtn");
		prevbtn  = $("#xe_gallery_prevbtn");
		nextbtn  = $("#xe_gallery_nextbtn");
	}

	return xScreen;
}

var nowThumbnail = 0;

function onThumbnailOver()
{
	nowThumbnail = parseInt($(this).attr("tabindex"));
	var src = $(this).attr("image_middle");
	$("#master_image").attr("src", src);

}

/* DOM READY */
$(function() {


	$("img.prod_thumb").mouseover(onThumbnailOver);
	$("img[onclick!='']").css("cursor","pointer");

	$("#master_image").click(function () {
		var container  = $(".xe_content");
		var imglist    = container.find("img[rel=xe_gallery]");
		var currentIdx = nowThumbnail;
		var xScreen    = getScreen();

		// 스크린을 보여주고
		xScreen.list  = imglist;
		xScreen.index = currentIdx;
		xScreen.xeShow();
	});

});

})(jQuery);

