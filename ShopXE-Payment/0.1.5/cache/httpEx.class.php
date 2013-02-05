<?PHP
    // +----------------------------------------------------------------------+
    // | PHP version 4 Later                                                  |
    // +----------------------------------------------------------------------+
    // | Copyright (c) 2007 Nickname:Shin Gong Pyo                            |
    // +----------------------------------------------------------------------+
    // | This source file is subject to version 3.0 of the PHP license,       |
    // | that is bundled with this package in the file LICENSE, and is        |
    // | available through the world-wide-web at the following url:           |
    // | http://www.php.net/license/3_0.txt.                                  |
    // | If you did not receive a copy of the PHP license and are unable to   |
    // | obtain it through the world-wide-web, please send a note to          |
    // | license@php.net so we can mail you a copy immediately.               |
    // +----------------------------------------------------------------------+
    // | Author: Nickname:Shin Gong Pyo <chese9kim@paran.com>                 |
    // +----------------------------------------------------------------------+
    //
    // $Id: class.HttpEx.php,v 1.0 2007/05/07 Seoul Exp $
    /* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class HttpEx{

    var $address;
	var $headers;
    var $cookie;
    var $variable;
    var $RequestHeader;
    var $ResponseHeader;
    var $fsockstream;

    # constructor
    function HttpEx($url="") {
		$this->address = array();
		$this->headers = array();
		$this->cookie  = array();
        if(trim($url)) $this->setURL($url);

		$this->addRequestHeader("Host", "Host: ".$this->address['host']);
        $this->addRequestHeader("User-agent", "User-agent: ".$_SERVER['HTTP_USER_AGENT']);
		$this->addRequestHeader("Referer", "Referer: http://".$this->address['host'].$this->address['path']);
    }

    /**
     * URL 지정함수
	 *
     * @param string $url : URL
     * @return boolean
     */
    function setURL($url) {
        if(!trim($url)) return false;
		$this->address = parse_url($url);
        if(strtoupper($this->address['scheme']) != "HTTP") return false;

        if(is_null($this->address['port'])) $this->address['port'] = 80;
        if(is_null($this->address['path'])) $this->address['path'] = "/";
        if($this->address['query']) {
            $arr1 = explode("&", $this->address['query']);
            foreach($arr1 as $value) {
				$arr2 = explode("=", $value);
				$this->variable[$arr2[0]] = $arr2[1];
            }
        }
        return true;
    }

    /**
     * 변수값을 지정한다.
     *
     * @param string $key : 변수명, 배열로도 넣을수 있다.
     * @param string $value : 변수값
     */
    function setParam($key, $value="") {
        if(is_array($key)) 
			foreach($key as $k => $v) $this->variable[$k] = $v;
        else $this->variable[$key] = $value;
    }

    /**
     * 헤더 확장용 함수
     *
     * @param string $Status : 변수명, 배열로도 넣을수 있다.
     * @param string $Contents : 변수값
     */
    function addRequestHeader($Status, $Contents="") {
        if(is_array($Status)) 
			foreach($Status as $k => $v) $this->headers[$k] = $v;
        else $this->headers[$Status] = $Contents;
    }

    /**
     * 쿠키를 지정한다.
     *
     * @param string $key : 쿠키변수명, 배열로도 넣을수 있다.
     * @param string $value : 쿠키변수값
     */
    function setCookie($key, $value="") {
        if(is_array($key)) foreach($key as $k => $v) $this->cookie[$key] = "$k=$v"; 
        else $this->cookie[$key] = "$key=$value";
		$this->addRequestHeader("Cookie", "Cookie: ".implode("; ", $this->cookie));
    }

    /**
     * 인증설정함수
     *
     * @param string $id : 아이디
     * @param string $pass : 패스워드
     */
    function setAuth($id, $pass) {
		$this->addRequestHeader("Authorization", "Authorization: Basic ".base64_encode($id.":".$pass));
    }

    /**
     * 페이지 응답헤더 설정함수
     */
    function setResponseHeader() {
        // 헤더 부분을 구한다.
        $this->ResponseHeader = ""; // 헤더의 내용을 초기화 한다.
        while(trim($buffer = fgets($this->fsockstream,1024)) != "") {
            $this->ResponseHeader .= $buffer;
        }
    }

    /**
     * 페이지 응답내용 설정함수
     */
    function setResponseBody() {
        // 바디 부분을 구한다.
		$ResponseBody = "";
        while(!feof($this->fsockstream)) {
            $ResponseBody .= fgets($this->fsockstream,1024);
        }
		return $ResponseBody;
    }

    /**
     * 페이지 요청헤더 구성함수
     *
     * @param string $Methode : POST, GET 중 하나를 입력한다.
     * @return string
     */
    function setRequestHeader($Methode) {
		$query = (strtoupper($Methode) == "POST")?"\r\n":"?";
        if(is_array($this->variable)) {
			$parameter = array();
            foreach($this->variable as $key => $val)
				$parameter[] = trim($key)."=".urlencode(trim($val));
			$query.= implode("&", $parameter);
			if(strtoupper($Methode) == "POST") $query .= "\r\n";
        }
		if($query == "?") $query = "";

		$this->RequestHeader  = $Methode." ".$this->address['path'];
		$this->RequestHeader .= (strtoupper($Methode) == "POST")?" HTTP/1.0\r\n":$query." HTTP/1.0\r\n";

		$this->RequestHeader .= implode("\r\n", $this->headers)."\r\n";

        // GET, POST 방식에 따라 헤더를 다르게 구성한다.
		if(strtoupper($Methode) == "POST") {
			$this->RequestHeader .= "Content-type: application/x-www-form-urlencoded\r\n";
			if($query) {
				$this->RequestHeader .= "Content-length: ".strlen($query)."\r\n";
				$this->RequestHeader .= $query;
			}
		}
		$this->RequestHeader .= "\r\n";
    }

    /**
     * 접속시도
     *
     * @param string $mode : POST, GET 중 하나를 입력한다.
     * @return bool
     */
    function Open($Methode="GET") {
        // 웹서버에 접속한다.
        if(!$this->fsockstream = fsockopen($this->address['host'], $this->address['port'], $errno, $errstr, 10))
			return false;
			//return "error[".$errno."] : ".$errstr;
		$this->setRequestHeader($Methode);
        fputs($this->fsockstream, $this->RequestHeader);
		return true;
    }

    /**
     * 데이타 전송함수
     */
    function SendRequestHeader() {
        // 헤더 부분을 구한다.
        $this->setResponseHeader();
    }

    /**
     * 데이타 전송함수
     *
     * @return string
     */
    function SendRequestBody() {
        // 헤더 부분을 구한다.
        $this->setResponseHeader();

        // 바디 부분을 구한다.
		$ResponseBody = $this->setResponseBody();

        return $ResponseBody;
    }

    /**
     * 헤더를 구하는 함수
     *
     * @param string $Status : 요청Status가 없으면 헤더전체를 반환
     * @return string
     */
    function getResponseHeader($Status="") {
		if($Status && preg_match("/".$Status.": ([^\n]+)/", $this->ResponseHeader, $ret))
			return $ret[0];
        return $this->ResponseHeader;
    }

    /**
     * 쿠키값을 구하는 함수
     *
     * @param string $key : 쿠키변수
     * @return string or array
     */
    function getCookie($key="") {
        if($key && preg_match("/".$key."=([^;]+)/", $this->ResponseHeader, $ret)){
			return $ret[1];
        } else {
            preg_match_all("/Set-Cookie: [^\n]+/", $this->ResponseHeader, $ret);
            return $ret[0];
        }
    }

	function Close() {
        // 접속을 해제한다.
        fclose($this->fsockstream);
	}
}
?>