Chocoby
====

KEYWORDS
----
* KEY table_name : 이 항목이 지정된 테이블에서의 유일키를 나타냄을 명시
* AS var_name : KEY 키워드로 검색된 레코드를 var_name 이름으로 받아온다.
* FOR table_name : 이 항목이 지정된 테이블의 컬럼을 가리킴을 명시.


FUNCTIONS
----
* UPDATE( table_name ) : KEY항목과 일치된 레코드를 FOR로 지정된 값들로 업데이트시킨다.
* INSERT( table_name ) : FOR로 지정된 값들을 가지고 새 레코드를 삽입한다.


USAGE
----

UPDATE패킷 예제<br>
강제로 레벨을 올리도록 하는 패킷.<br>
```
PACKET force_level_up {
  char(32) id KEY user_table AS user
  int value

  PROCEDURE {
    user->level += value;
  }
}
```
UDPATE 패킷 예제2<br>
닉네임을 바꾸는 패킷.
```
PACKET change_nickname {
  char(32) id KEY user_table
  char(32) nickname FOR user_table

  PROCEDURE {
    UPDATE( user_table )
  }
}
```
INSERT 패킷 예제<br>
아이템 추가 패킷.
```
PACKET add_item {
  char(32) id FOR inventory_table
  int code FOR inventory_table
  int amount FOR inventory_table

  PROCEDURE {
    INSERT( inventory_table )
  }
}
```
