<?php
/*
 * phptop - Analyse quickly system ressource usage across many PHP queries
 * Copyright (C) 2009,2010,2011 Bearstech - http://bearstech.com/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function _phptop_init() {
  global $_phptop_t0;
  $t0 = microtime(TRUE);
  $ru = getrusage();
  $_phptop_t0 = array(
    'time' => $t0,
    'tusr' => $ru['ru_utime.tv_sec'] + $ru['ru_utime.tv_usec'] / 1e6,
    'tsys' => $ru['ru_stime.tv_sec'] + $ru['ru_stime.tv_usec'] / 1e6
  );
  register_shutdown_function('_phptop_fini');
}

function _phptop_fini() {
  global $_phptop_disable;
  if ($_phptop_disable) return;

  global $_phptop_t0;
  $t1   = microtime(TRUE);
  $time = $t1 - $_phptop_t0['time'];

  $ru = getrusage();
  $tusr = $ru['ru_utime.tv_sec'] + $ru['ru_utime.tv_usec'] / 1e6 - $_phptop_t0['tusr'];
  $tsys = $ru['ru_stime.tv_sec'] + $ru['ru_stime.tv_usec'] / 1e6 - $_phptop_t0['tsys'];

  $mem   = memory_get_peak_usage(TRUE);

  $proto = isset($_SERVER['HTTPS']) ? 'https' : 'http';
  $vhost = $_SERVER['SERVER_NAME'];
  $uri   = $_SERVER['REQUEST_URI'];
  $self  = $vhost != '' ? "$proto://$vhost$uri" : $_SERVER['SCRIPT_FILENAME'];

  $msg = sprintf("phptop %s time:%.6F user:%.6F sys:%.6F mem:%d", $self, $time, $tusr, $tsys, $mem);
  error_log($msg);
}

/* Don't run in CLI, it pollutes stderr and makes cronjob un-needingly noisy */
if (php_sapi_name() != 'cli') _phptop_init();
?>
