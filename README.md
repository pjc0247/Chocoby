Chocoby
=======

TYPE
----
* int : integer (4b)
* int64 : integer (8b)
* bool : boolean (1b)
* string : null-terminated string (variable length)
* char : single character (1b)
* binary : fixed-size binary data (specified length)
* float : float (4b)
* double : double (8b)


STRUCT
----
STRUCT키워드로 구조체를 정의할 수 있습니다.<br>
구조체는 여러개의 변수를 하나로 묶어주는 기능을 합니다.
```
STRUCT item {
  int code
  int amount
}
```


TABLE
----
TABLE키워드로 테이블을 정의합니다.<br>
변수 이름 뒤에 콜론을 찍고 기본값을 지정할 수 있습니다.
```
TABLE user {
  char(32) id PRIMARY
  char(32) password
  char(16) nickname
  int level : 0
}
```
```
TABLE inventory {
  char(32) id PRIMARY
  item item
}
```


PACKET
----
C2S는 ClientToServer패킷을 정의하는 키워드입니다.<br>
클라이언트로부터 서버에 보낼 패킷을 정의합니다.
```
C2S login {
  char(32) id
  char(32) password
}
```
```
C2S queryInventory {
  char(32) id
}
```
```
C2S forceLevelUp {
  char(32) id
  int value : 1
}
```
S2C는 ServerToClient패킷을 정의하는 키워드입니다.<br>
서버로부터 클라이언트에 보낼 패킷을 정의합니다.
```
S2C responseLogin {
  char(32) id
  int result
}
```
```
S2C responseInventory {
  char(32) id
  item(ary) items;
}
```


PROCEDURE
----
PROCEDURE는 서버에서 C2S패킷을 수신했을 경우에 대한 핸들러입니다.<br>
콜론 좌측에는 핸들링할 C2S패킷의 이름을 넣고,<br>
콜론 우측에는 리스폰스할 S2C패킷의 이름을 넣습니다.<br>
리스폰스는 생략 가능하며, 이 경우에는 콜론을 사용하지 않고 C2S패킷의 이름만 적습니다.
```
PROCEDURE login : responseLogin {
  OUT->id = IN->id
  OUT->result =
    TABLE( user )
    ->WHERE( id, in->id )
    ->WHERE( password, in->password )
    ->FIND(1)
    ->EXIST()
}
```
```
PROCEDURE queryInventory : responseInventory {
  OUT->id = IN->id
  OUT->items =
    TABLE( inventory )
    ->WHERE( id, in->id )
    ->FIND()
}
```
```
PROCEDURE forceLevelUp {
  EXEC TABLE( user )
    ->WHERE( id, in->id )
    ->FIND(1)
    ->INCR( level, IN->value )
}
```

USAGE
----
C++
```c++
Chocoby::login(
  id, password,
  [](char id[32], int result){
    if( result )
      printf("login success\n");
  });
```


RUBY
```ruby
Chocoby::login
  :id => @id, :password => @password do |id, result|
  
  puts "login success" if result > 0 
end
```
