이미 있는 언어를 기반으로 라이브러리 형태로 제공되는 형식<br>



PHP STYLE
----
* 코드의 상단부에 IN, OUT 항목들과 그 속성을 기술한다.<br>
  * in, out에 대한 기술은
  * GET/POST로 넘어온 값을 PHP value와 매칭시켜주는 역할,<br>
  * RESPONSE로 넘겨줄 값을 PHP value와 매칭시켜주는 역할, <br>
  * 항목이 유효한지 검증하는 역할,<br>
  * DB 테이블의 컬럼과 연관 있는 항목일 경우 해당 로우를 바로 PHP value로 받아오는 역할을 한다.<br>
* 그 아래에 로직을 작성한다. DB 접근은 ORM을 사용<br>
* response는 json또는 xml로
* 테이블의 로우 변경 값은 response 이후 자동으로 커밋된다.

<br><br>
* attributes - in
  * required : 이 항목은 필수임을 명시
  * optional : 이 항목은 필수가 아님을 명시 (기본값)
  * as[name] : 이 항목을 [name]의 이름으로 된 PHP변수로 가져온다. (기본값은 파라미터 이름)
  * pk[table,name]  : 이 항목은 [table]에서의 PK임을 명시, 이 항목을 KEY로 삼아서 [table]에서 행을 가져와 [name]이름의 변수에 담는다.
  * disable_autocommit : pk로 행을 가져온 경우, 그 행에 대한 오토커밋을 금지한다.
* attributes - out
  * required : 이 항목은 필수임을 명시
  * optional : 이 항목은 필수가 아님을 명시 (기본값)
  * as[name] : 이 항목을 [name]의 이름으로 된 PHP변수로 사용함을 명시. (기본값은 파라미터 이름)
  * timestamp : 타임스탬프 값 자동 대입, 이 항목은 required로 간주된다.
<br>
```PHP
<?php
/* in, out 파라미터를 여기에 기술한다. */
poco::in(
	"id" => "required pk[table,user]",
	"password" => "required as[pw]",
	"nickname" => "",
	"age" => "optional" );
poco::out(
	"result" => "required",
	"timestamp" => "timestamp" );

$user->password = $pw;
$user->nickname = $nickname;
if( $age != null )
	$user->age = $age;
	
$result = 0;
	
/* response는 자동으로 보내진다. (JSON 또는 XML) */
/* disable_autocommit 옵션이 지정되지 않았다면,
 * $user는 자동으로 DB에 커밋된다. */
?>
```
```
{
  "result":0,
  "timestamp":12345678
}
```
