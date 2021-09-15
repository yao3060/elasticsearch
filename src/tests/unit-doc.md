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
        <td rowspan="3">Template</td>
        <td rowspan="3">公共模板</td>
        <td rowspan="2">v1/templates</td>
        <td>TemplateTest@testSearch</td>
        <td>[@testSearch] /apiv2/get-ppt-template-list?sort_type=bytime</td>
    </tr>
    <tr>
      <td>TemplateTest@testSearchCarryKeyword</td>
      <td>[@testSearchCarryKeyword]  /api/get-template-list?w=%E4%BD%A0%E5%A5%BD&p=1&kid_1=0&kid_2=0&ratioId=0&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=10_30_0&width=1242&height=2208</td>
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
      <td>[@testSearch] /h5-api/bg-video-search</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearch</td>
      <td>[@testVideoSearch] /video/bg-video-search</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearchCarryKeyword</td>
      <td>[@testVideoSearchCarryKeyword] /video/bg-video-search?keyword=%E6%8F%92%E7%94%BB&class_id=0&page=1&ratio=1&pageSize=30</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearchCarryKeywordBusiness</td>
      <td>[@testVideoSearchCarryKeywordBusiness] /video/bg-video-search?keyword=%E6%8F%92%E7%94%BB&class_id=0&page=1&ratio=1&pageSize=30</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearchCarryKeywordInvitation</td>
      <td>[@testVideoSearchCarryKeywordInvitation] /video/bg-video-search?keyword=%E9%82%80%E8%AF%B7%E5%87%BD&class_id=0&page=3&ratio=2&pageSize=30</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearchPageOfNine</td>
      <td>[@testVideoSearchPageOfNine] /video/bg-video-search?keyword=&class_id=&page=1&ratio=2&pageSize=9</td>
    </tr>
    <tr>
      <td>SensitiveWord</td>
      <td>敏感词</td>
      <td>v1/sensitive/word/validate</td>
      <td>SensitiveWordTest@testVideoSearch</td>
      <td>[@testVideoSearch] /video/bg-video-search</td>
    </tr>
    <tr>
      <td rowspan="2">DesignerTemplateSearch</td>
      <td rowspan="2">设计师模板，二次设计</td>
      <td rowspan="2">v1/designer-templates</td>
      <td>DesignerTemplateSearchTest@testSearch</td>
      <td>[@testSearch] /api/get-template-list?w=&p=1&kid_1=1&kid_2=19&ratioId=-1&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=&width=200&height=200&es_type=1</td>
    </tr>
    <tr>
      <td>DesignerTemplateSearchTest@testSearchCarryKeyword</td>
      <td>[@testSearchCarryKeyword] /api/get-template-list?w=%E4%B8%BB%E5%9B%BE&p=1&kid_1=156&kid_2=301&ratioId=-1&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=0&es_type=3</td>
    </tr>
    <tr>
      <td>RichEditorAsset</td>
      <td>富文本元素</td>
      <td>v1/rich-editor-assets</td>
      <td>RichEditorAsset@testSearch</td>
      <td>[@testSearch] /rt-api/rt-asset-search</td>
    </tr>
    <tr>
      <td>VideoTemplate</td>
      <td>片段视频</td>
      <td>v1/video-templates</td>
      <td>VideoTemplateTest@testSearch</td>
      <td>[@testSearch] /api-video/get-excerpt-list</td>
    </tr>
    <tr>
      <td rowspan="2">LottleVideoWord</td>
      <td rowspan="2">设计师动画特效</td>
      <td rowspan="2">v1/lottie-video-words</td>
      <td>LottleVideoWord@testSearch</td>
      <td>[@testSearch] /api-video/get-excerpt-list</td>
    </tr>
    <tr>
      <td>LottleVideoWord@testSearchCarryKeyword</td>
      <td>[@testSearchCarryKeyword] /video/lottie-word-search?keyword=风景</td>
    </tr>
</table>
