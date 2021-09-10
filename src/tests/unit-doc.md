## 接口单元测试文档

### 完成进度

- [x] Template
  - [x] search
  - [ ] recommendSearch (无法验证)
- [ ] DesignerTemplate (没有设计师账号)
- [x] BackgroundVideo
  - [x] search
- [x] SensitiveWord
  - [x] search

### 目标
- [ ] 尽可能的全覆盖能通过api验证的接口用例

### search 方法

<table>
    <tr>
        <th style="background-color: #f7f7f7; color: #646466;">model</th> 
        <th style="background-color: #f7f7f7; color: #646466;">route</th> 
        <th style="background-color: #f7f7f7; color: #646466;">new api</th> 
        <th style="background-color: #f7f7f7; color: #646466;">unit test method</th> 
        <th style="background-color: #f7f7f7; color: #646466;">old api</th> 
   </tr>
    <tr>
        <td rowspan="2" style="color: #646466;">Template</td>
        <td rowspan="2" style="color: #646466;">公共模板</td>
        <td style="color: #646466;">v1/templates</td>
        <td>
          <p style="color: #646466;">TemplateTest@testSearch</p>
          <hr>
          <p style="color: #646466;">TemplateTest@testSearchCarryKeyword</p>
        </td>
        <td style="color: #646466;">
          <p style="color: #646466;"><span style="color: #3d7eff;">[@testSearch]</span> /apiv2/get-ppt-template-list?sort_type=bytime</p>
          <hr>
          <p style="color: #646466;"><span style="color: #e74c3c;">[@testSearchCarryKeyword] </span> /api/get-template-list?w=%E4%BD%A0%E5%A5%BD&p=1&kid_1=0&kid_2=0&ratioId=0&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=10_30_0&width=1242&height=2208</p>
        </td>
    </tr>
    <tr>
      <td style="color: #646466;">v1/templates/recommends</td>
      <td style="color: #646466;"></td>
      <td style="color: #646466;"></td>
    </tr>
    <tr>
      <td rowspan="2" style="color: #646466;">BackgroundVideo</td>
      <td rowspan="2" style="color: #646466;">背景视频</td>
      <td rowspan="2" style="color: #646466;">v1/background/videos</td>
      <td style="color: #646466;">BackgroundVideoTest@testSearch</td>
      <td style="color: #646466;"><span style="color: #3d7eff;">[@testSearch]</span> /h5-api/bg-video-search</td>
    </tr>
    <tr>
      <td style="color: #646466;">BackgroundVideo@testVideoSearch</td>
      <td style="color: #646466;"><span style="color: #e74c3c;">[@testVideoSearch]</span> /video/bg-video-search</td>
    </tr>
    <tr>
      <td style="color: #646466;">SensitiveWord</td>
      <td style="color: #646466;">敏感词</td>
      <td style="color: #646466;">v1/sensitive/word/validate</td>
      <td style="color: #646466;">SensitiveWordTest@testVideoSearch</td>
      <td style="color: #646466;"><span style="color: #3d7eff">[@testVideoSearch]</span> /video/bg-video-search</td>
    </tr>
</table>
