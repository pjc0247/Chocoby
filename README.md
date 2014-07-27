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
```
STRUCT item {
  int code
  int amount
}
```

TABLE
----
```
TABLE user {
  char(32) id primary
  char(32) password
  char(16) nickname
  int level
}
```
```
TABLE inventory {
  char(32) id primary
  item item
}
```

PACKET
----
```
C2S login {
  char(32) id
  char(32) password
}
```
```
C2S queryInventory{
  char(32) id
}
```
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
```
PROCEDURE login : responseLogin {
  out->id = in->id
  out->result =
    TABLE( user )
    ->WHERE( user, id, in->id )
    ->WHERE( user, password, in->password )
    ->FIND(1)
    ->EXIST();
}
```
```
PROCEDURE queryInventory : responseInventory {
  out->id = in->id
  out->items =
    TABLE( inventory )
    ->WHERE( inventory, id, in->id )
    ->FIND();
}
```
