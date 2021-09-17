## 接口单元测试文档

### 完成进度

- [x] Template
  - [x] search
  - [ ] recommendSearch (无法验证)
- [x] DesignerTemplate
  - [x] search
- [x] BackgroundVideo
  - [x] search
- [x] SensitiveWord
  - [x] search
- [x] RichEditorAsset
  - [x] search
- [x] VideoTemplateTest
  - [x] search
- [x] LottieVideoTest
  - [x] search
- [x] LottieVideoWordTest
  - [x] search
### 目标
- [ ] 尽可能的全覆盖能通过api验证的接口用例

### search 方法

<table>
    <tr>
        <th style="background-color: #f7f7f7;">model</th> 
        <th style="background-color: #f7f7f7;">route</th> 
        <th style="background-color: #f7f7f7;">new api</th> 
        <th style="background-color: #f7f7f7;">unit test method</th> 
        <th style="background-color: #f7f7f7;">old api</th> 
   </tr>
    <tr>
        <td rowspan="9">Template</td>
        <td rowspan="9">公共模板</td>
        <td rowspan="8">v1/templates</td>
        <td>TemplateTest@testSearch</td>
        <td>/apiv2/get-ppt-template-list?sort_type=bytime</td>
    </tr>
    <tr>
      <td>TemplateTest@testSearchCarryKeyword</td>
      <td>/api/get-template-list?w=%E4%BD%A0%E5%A5%BD&p=1&kid_1=0&kid_2=0&ratioId=0&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=10_30_0&width=1242&height=2208</td>
    </tr>
    <tr>
      <td>TemplateTest@testSearchPageOfTwo</td>
      <td>/apiv2/get-ppt-template-list?keyword=&p=2&class_id=290_0_0&sort_type=&tag_id=0</td>
    </tr>
    <tr>
      <td>TemplateTest@testSearchCarryClassIdsTagId</td>
      <td>/apiv2/get-ppt-template-list?keyword=&p=1&class_id=290_334_0_0&sort_type=&tag_id=46</td>
    </tr>
    <tr>
      <td>TemplateTest@testSearchCarryClassIdsSortTypeTagId</td>
      <td>/apiv2/get-ppt-template-list?keyword=&p=1&class_id=290_334_0_0&sort_type=bytime&tag_id=46</td>
    </tr>
    <tr>
      <td>TemplateTest@testSearchCarryKeywordClassIdsSortTypeTagId</td>
      <td>/apiv2/get-ppt-template-list?keyword=%E7%8E%AF%E4%BF%9D&p=1&class_id=290_0_0_0&sort_type=&tag_id=0</td>
    </tr>
    <tr>
      <td>TemplateTest@testSearchCarryKeywordClassIdsTagId</td>
      <td>/apiv2/get-ppt-template-list?keyword=%E7%8E%AF%E4%BF%9D&p=1&class_id=290_0_0_710&sort_type=&tag_id=49</td>
    </tr>
    <tr>
      <td>TemplateTest@testSearchCarryKeywordClassIdsTagIdSecond</td>
      <td>/apiv2/get-ppt-template-list?keyword=%E7%8E%AF%E4%BF%9D&p=1&class_id=290_0_0_710&sort_type=&tag_id=104</td>
    </tr>
    <tr>
      <td>v1/templates/recommends</td>
      <td>--</td>
      <td>--</td>
    </tr>
    <tr>
      <td rowspan="6">BackgroundVideo</td>
      <td rowspan="6">背景视频</td>
      <td rowspan="6">v1/background/videos</td>
      <td>BackgroundVideoTest@testSearch</td>
      <td>/h5-api/bg-video-search</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearch</td>
      <td>/video/bg-video-search</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearchCarryKeyword</td>
      <td>/video/bg-video-search?keyword=%E6%8F%92%E7%94%BB&class_id=0&page=1&ratio=1&pageSize=30</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearchCarryKeywordBusiness</td>
      <td>/video/bg-video-search?keyword=%E6%8F%92%E7%94%BB&class_id=0&page=1&ratio=1&pageSize=30</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearchCarryKeywordInvitation</td>
      <td>/video/bg-video-search?keyword=%E9%82%80%E8%AF%B7%E5%87%BD&class_id=0&page=3&ratio=2&pageSize=30</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearchPageOfNine</td>
      <td>/video/bg-video-search?keyword=&class_id=&page=1&ratio=2&pageSize=9</td>
    </tr>
    <tr>
      <td>SensitiveWord</td>
      <td>敏感词</td>
      <td>v1/sensitive/word/validate</td>
      <td>SensitiveWordTest@testVideoSearch</td>
      <td>/video/bg-video-search</td>
    </tr>
    <tr>
      <td rowspan="3">DesignerTemplateSearch</td>
      <td rowspan="3">设计师模板，二次设计</td>
      <td rowspan="3">v1/designer-templates</td>
      <td>DesignerTemplateTest@testSearch</td>
      <td>/api/get-template-list?w=&p=1&kid_1=1&kid_2=19&ratioId=-1&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=&width=200&height=200&es_type=1</td>
    </tr>
    <tr>
      <td>DesignerTemplateTest@testSearchCarryKeyword</td>
      <td>/api/get-template-list?w=%E4%B8%BB%E5%9B%BE&p=1&kid_1=156&kid_2=301&ratioId=-1&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=0&es_type=3</td>
    </tr>
    <tr>
      <td>DesignerTemplateTest@testSearchNormalEsTypeOfThree</td>
      <td>/api/get-template-list?w=&p=1&kid_1=156&kid_2=157&ratioId=-1&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=0&es_type=3</td>
    </tr>
    <tr>
      <td rowspan="5">RichEditorAsset</td>
      <td rowspan="5">富文本元素</td>
      <td rowspan="5">v1/rich-editor-assets</td>
      <td>RichEditorAssetTest@testSearch</td>
      <td>/rt-api/rt-asset-search</td>
    </tr>
    <tr>
      <td>RichEditorAssetTest@search_carry_class_ids</td>
      <td>/rt-api/rt-asset-search?class_ids=1,0&page=1&keyword=</td>
    </tr>
    <tr>
      <td>RichEditorAssetTest@search_carry_class_ids_second</td>
      <td>/rt-api/rt-asset-search?class_ids=2,55&page=1&keyword=</td>
    </tr>
    <tr>
      <td>RichEditorAssetTest@search_carry_class_ids_page</td>
      <td>/rt-api/rt-asset-search?class_ids=1,0&page=3&keyword=</td>
    </tr>
    <tr>
      <td>RichEditorAssetTest@search_carry_keyword_class_ids</td>
      <td>/rt-api/rt-asset-search?class_ids=5,58&page=1&keyword=%E6%A9%98%E8%89%B2</td>
    </tr>
    <tr>
      <td rowspan="4">VideoTemplate</td>
      <td rowspan="4">片段视频</td>
      <td rowspan="4">v1/video-templates</td>
      <td>VideoTemplateTest@testSearch</td>
      <td>/api-video/get-excerpt-list</td>
    </tr>
    <tr>
      <td>VideoTemplateTest@testSearchCarryKeyword</td>
      <td>/api-video/get-excerpt-list?w=%E6%95%99%E5%B8%88%E8%8A%82&p=1&class_id=&ratio=2</td>
    </tr>
    <tr>
      <td>VideoTemplateTest@testSearchCarryClassIdsPageOfTwo</td>
      <td>/api-video/get-excerpt-list?w=&p=2&class_id=1579-1580&ratio=1</td>
    </tr>
    <tr>
      <td>VideoTemplateTest@testSearchCarryKeywordClassIdsOfNone</td>
      <td>/api-video/get-excerpt-list?w=%E4%B8%A2%E5%A4%B1&p=1&class_id=&ratio=1</td>
    </tr>
    <tr>
      <td rowspan="4">LottieVideo</td>
      <td rowspan="4">设计师动画特效</td>
      <td rowspan="4">v1/lottie-videos</td>
      <td>LottieVideoTest@testSearch</td>
      <td>/video/lottie-search</td>
    </tr>
    <tr>
      <td>LottieVideoTest@testSearchClassIdOfOnePageOfOne</td>
      <td>/video/lottie-search?keyword=&class_id=1&page=1</td>
    </tr>
    <tr>
      <td>LottieVideoTest@testSearchCarryKeywordClassIdOfOnePageOfOne</td>
      <td>/video/lottie-search?keyword=%E5%8F%AF%E7%88%B1&class_id=1&page=1</td>
    </tr>
    <tr>
      <td>LottieVideoTest@testSearchClassIdOfThreePageOfOne</td>
      <td>/video/lottie-search?keyword=&class_id=3&page=1</td>
    </tr>
    <tr>
      <td rowspan="2">LottieVideoWord</td>
      <td rowspan="2">设计师动画特效关键词</td>
      <td rowspan="2">v1/lottie-video-words</td>
      <td>LottieVideoWord@testSearch</td>
      <td>/api-video/get-excerpt-list</td>
    </tr>
    <tr>
      <td>LottieVideoWordTest@testSearchCarryKeyword</td>
      <td>/video/lottie-word-search?keyword=风景</td>
    </tr>
</table>
