<?php

/**
 * UNIT & INTEGRATION TESTS
 * @see https://codeception.com/docs/05-UnitTests
 *
 * 1. 新建一个测试
 * **********
 * ./codecept generate:test unit models/SearchSvg
 * 注意修改 `models/SearchSvgTest.php` 的 `namespace` 为 `tests\unit\models`
 *
 * 2. 执行测试
 * **********
 * ./codecept run unit models/SearchSvgTest
 *
 * 3. 执行所有单元测试
 * ./codecept run unit
 *
 * 贴纸
 * https://818ps.com/api/get-asset-list?w=&p=1&type=image&k1=0&k2=0&k3=0&tagId=0&sceneId=undefined&styleId=undefined&ratioId=undefined
 * https://818ps.com/api/get-recommend-asset?type=1
 * https://818ps.com/apiv2/get-search-keyword-list?type=1
 * https://818ps.com/apiv2/get-fav-image?kw=
 * https://818ps.com/api/asset-rec
 *
 * 表情包
 * http://818ps.com/api/get-asset-list?w=&p=1&type=image&k1=0&k2=0&k3=0&tagId=69&sceneId=undefined&styleId=undefined&ratioId=undefined
 *
 * 形状线条
 * https://818ps.com/apiv2/search-asset-svg?p=1&k2=0&word=&pageSize=50
 * https://818ps.com/api/get-recommend-asset?type=2
 *
 * 词云
 * https://818ps.com/api/get-recommend-asset?type=2
 * https://818ps.com/api/word-cloud-list
 *
 * 特效字
 * https://818ps.com/apiv2/get-font-count
 * https://818ps.com/api/get-recommend-asset?type=4
 * https://818ps.com/apiv2/get-fav-background
 * https://818ps.com/apiv2/get-history-record?type=2
 * https://818ps.com/apiv2/get-specific-word-class
 * https://818ps.com/apiv2/get-search-keyword-list?type=3
 * https://818ps.com/apiv2/get-specific-word-list?class_id=0
 */

// add unit testing specific bootstrap code here
