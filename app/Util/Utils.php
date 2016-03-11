<?php

namespace App\Util;

class Utils {
    // 指定された文字列で始まるか判定
    public static function startsWith($haystack, $needle) {
        return strpos($haystack, $needle, 0) === 0;
    }
    public static function startsWithIgnoreCase($haystack, $needle) {
        return self::startsWith(strtolower($haystack), strtolower($needle));
    }

    // 指定された文字列で終わるか判定
    public static function endsWith($haystack, $needle) {
        $start  = strlen($needle) * -1; //negative
        return substr($haystack, $start) === $needle;
    }
    public static function endsWithIgnoreCase($haystack, $needle) {
        return self::endsWith(strtolower($haystack), strtolower($needle));
    }

	public static function trimBOMtrim($target) {
		if (($target == NULL) || (mb_strlen($target) == 0)) {
			return $target;
		}
		if (ord($target{0}) == 0xef && ord($target{1}) == 0xbb && ord($target{2}) == 0xbf) {
			$target = substr($target, 3);
		}
		return trim($target);
	}

	public static function trimArray($targetArray) {
		$newArray = array();
		foreach ($targetArray as $item) {
			$item = trim($item);
			if ($item == '') {
				continue;
			}
			$newArray[] = $item;
		}
		return $newArray;
	}
}