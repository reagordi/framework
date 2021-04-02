<?php
/**
 * Reagordi Framework
 *
 * @package reagordi
 * @author Sergej Rufov <support@freeun.ru>
 */

/**
 * Отправка успешного ответа
 *
 * @param null|array $header Заголовок
 * @param array $result Ответ
 * @return array
 */
function api_ok( $header = null, $result = [] ) {
    $data = [
        'status' => true,
        'result' => $result
    ];
    return api_response( $header, $data );
}

/**
 * Отправка ошибки
 *
 * @param null|array $header Заголовок
 * @param int $code Код ошибки
 * @param string $msg Текст ошибки
 * @return array
 */
function api_error( $header = null, $code = 1, $msg = 'Unknown error' ) {
    $data = [
        'status' => false,
        'error' => [
            'code' => $code,
            'msg' => $msg
        ]
    ];
    return api_response( $header, $data );
}

/**
 * Отправка ответа после создания записи
 *
 * @param null|array $header Заголовок
 * @param array $result Ответ
 * @return array
 */
function api_created( $header = null, $result = [] ) {
    $header['code'] = isset( $header['code'] ) ? $header['code']: 201;
    $header['msg'] = isset( $header['msg'] ) ? $header['msg']: 'Created';
    return api_ok( $header, $data );
}

/**
 * Ошибка авторизации
 *
 * @return array
 */
function api_unauthorized() {
    $header = [
        'code' => 401,
        'msg' => 'Unauthorized'
    ];
    return api_error( $header, $header['code'], $header['msg'] );
}

/**
 * Доступ запрещён
 *
 * @return array
 */
function api_forbidden() {
    $header = [
        'code' => 403,
        'msg' => 'Forbidden'
    ];
    return api_error( $header, $header['code'], $header['msg'] );
}

/**
 * Страница не найденна
 *
 * @return array
 */
function api_notfound() {
    $header = [
        'code' => 404,
        'msg' => 'Not Found'
    ];
    return api_error( $header, $header['code'], $header['msg'] );
}

/**
 * Возврат ответа
 *
 * @param null $header Заголовок
 * @param array $result Результат
 * @return array
 */
function api_response( $header = null, $result = [] ): array {
    $header['code'] = isset( $header['code'] ) ? $header['code']: 200;
    $header['msg'] = isset( $header['msg'] ) ? $header['msg']: 'Ok';
    return array( $header['code'], $header['msg'], $result );
}

/**
 * Вывод ответа
 *
 * @param array $response Результат
 * @return void
 */
function api_send_response( array $response ) {
    $code = isset( $response[0] ) ? $response[0]: 200;
    $msg = isset( $response[1] ) ? $response[1]: 'Ok';
    $body = isset( $response[2] ) ? $response[2]: array();
    header( 'HTTP/1.1 ' . $code . ' ' . $msg );
    echo json_encode( $body );
}
