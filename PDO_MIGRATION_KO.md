# PDO DB 연결 설정

모든 PHP 파일은 더 이상 `mysql_*` 함수를 호출하지 않고 `db.php`의 PDO 계층을 사용합니다. 쿼리 값은 준비문 파라미터로 전달되며 포인트 충전·선물과 설치 작업은 PDO 트랜잭션 또는 예외 처리로 동작합니다.

## 그누보드 설정 사용

그누보드 설정 파일이 다음 상수를 정의하고 있으면 자동으로 사용합니다.

```php
G5_MYSQL_HOST
G5_MYSQL_PORT // 선택, 기본 3306
G5_MYSQL_USER
G5_MYSQL_PASSWORD
G5_MYSQL_DB
```

## 환경변수 사용

그누보드 상수를 사용할 수 없는 독립 실행 환경에서는 다음 값을 설정합니다.

```text
SLOT_DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=gnuboard;charset=utf8mb4
SLOT_DB_USER=db_user
SLOT_DB_PASSWORD=change-this-password
```

또는 `SLOT_DB_HOST`, `SLOT_DB_PORT`, `SLOT_DB_NAME`을 사용할 수 있습니다. 비밀번호는 PHP 소스나 Git 저장소에 넣지 말고 서버 환경변수 또는 비밀 저장소에서 주입해야 합니다.

## 회원 테이블 매핑

현재 슬롯 기능의 기존 컬럼은 `zetyx_member_table`의 `user_id`, `gamepoint`, `bar`, `bell`, `bellbonus`, `rankpoint`, `box1`~`box9`입니다. 실제 그누보드7의 회원 ID·포인트 구조가 다르면 해당 컬럼을 운영 DB와 맞춘 뒤 SQL 스키마와 화면 코드를 함께 조정해야 합니다. PDO 전환은 완료했지만 컬럼명 자동 변환까지 수행하지는 않습니다.

설치가 끝나면 `install.php`는 삭제하거나 웹서버에서 차단하세요.
