# PHP2Shell 보안 분석 및 수정 결과

## 분석 범위

저장소의 PHP 파일 전체를 대상으로 운영체제 명령 실행, 동적 코드 실행, 위험한 역직렬화, 파일 업로드·이동, 동적 include, 외부 입력의 파일 경로 사용을 검색했습니다.

## 분석 결과

현재 소스에는 다음 직접적인 PHP 웹셸 실행 경로가 없습니다.

- `shell_exec`, `system`, `passthru`, `popen`, `proc_open`, `pcntl_exec`
- `eval`, `assert`, `create_function`
- `$_FILES`, `move_uploaded_file`, `php://input`
- `base64_decode`와 압축 해제를 이용한 동적 페이로드
- 외부 입력을 `include`·`require` 파일 경로로 직접 사용하는 코드

`db.php`의 `PDO::exec()`는 고정된 설치 SQL 파일을 실행하는 용도이며, 외부 입력이 경로로 전달되지 않습니다. 설치 화면은 관리자 로그인·CSRF 검증 뒤에만 POST DB 작업을 수행합니다.

## 적용한 방어 수정

1. PHP 응답에 `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`를 추가했습니다.
2. HTTPS 운영 환경에서는 HSTS를 활성화하도록 했습니다.
3. 세션 쿠키에 `HttpOnly`, `SameSite=Lax`, HTTPS 연결 시 `Secure`를 적용했습니다.
4. 루트 `.htaccess`에서 SQL·Markdown·환경파일·Git·로그·매니페스트 파일의 직접 다운로드와 디렉터리 목록 노출을 차단했습니다.
5. `images/.htaccess`와 `swf/.htaccess`에서 PHP·PHTML·PHAR·CGI·PL·Python·Shell 파일 실행을 차단했습니다.
6. 정적 자산 디렉터리에서 CGI 실행과 MIME 핸들러를 제거했습니다.

## 운영 배포 시 필수 조치

- `install.php` 설치가 끝나면 삭제하거나 웹서버에서 차단합니다.
- Apache에서 `.htaccess`가 적용되는지 `AllowOverride` 설정을 확인합니다. Nginx를 사용하는 경우 동일한 차단 규칙을 서버 설정에 반영해야 합니다.
- 업로드 기능은 이 저장소에 없지만, 그누보드 본체의 업로드 디렉터리에서도 PHP 실행을 차단해야 합니다.
- PHP-FPM/웹서버에서 명령 실행 함수 비활성화 정책을 검토하고, 최소 DB 권한 계정을 사용합니다.
- 운영 배포 후 `*.php`, `*.phtml`, `*.phar` 파일이 `images/`, `swf/`, 업로드 디렉터리에서 실행되지 않는지 확인합니다.
- 실제 운영 DB 연결 테스트는 운영 DB 접속정보와 테스트 회원이 제공된 뒤 별도로 수행해야 합니다.

이번 점검은 소스 정적 분석과 저장소 방어 설정 적용 결과입니다. 운영 웹서버의 PHP-FPM, Apache/Nginx, 그누보드 본체 설정까지 확인해야 최종적으로 PHP2Shell 공격면이 닫혔다고 판단할 수 있습니다.
