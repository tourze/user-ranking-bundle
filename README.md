# UserRankingBundle

用户排行榜功能模块，提供灵活的用户排名管理功能。

## 功能特性

- 支持多个排行榜列表管理
- 灵活的排名计算规则（通过SQL配置）
- 支持固定排名和动态排名
- 支持排行榜位置管理和推荐
- 完整的CRUD操作界面
- 命令行工具支持批量计算排名
- 可配置的更新频率（每分钟到每天）

## 数据结构

### UserRankingList (排行榜列表)

- 标题 (`title`)
- 副标题 (`subtitle`)
- 颜色标识 (`color`)
- LOGO (`logoUrl`)
- 计算规则SQL (`scoreSql`)
- 总名次数量 (`count`)
- 关联位置 (`positions`)
- 更新频率 (`updateFrequency`)：支持每分钟、每5分钟、每15分钟、每30分钟、每小时、每天

### UserRankingItem (排行榜项目)

- 排名 (`number`)
- 用户ID (`userId`)
- 上榜理由 (`textReason`)
- 分数 (`score`)
- 固定排名标记 (`fixed`)
- 推荐人头像 (`recommendThumb`)
- 推荐理由 (`recommendReason`)

### UserRankingPosition (排行榜位置)

- 位置名称 (`title`)
- 关联排行榜 (`lists`)

## 技术实现

### 依赖关系

- 依赖 `AntdCpBundle` 提供后台管理界面
- 使用 Doctrine ORM 进行数据持久化
- 实现 `BundleDependencyInterface` 管理模块依赖

### 核心特性

1. **灵活的排名计算**
   - 通过自定义SQL实现排名计算规则
   - 支持固定排名覆盖自动计算结果

2. **数据完整性**
   - 实现唯一约束避免重复排名
   - 使用雪花算法生成分布式ID
   - 完整的时间戳和操作者追踪

3. **权限控制**
   - 使用 `AsPermission` 注解进行权限管理
   - 支持CRUD操作的细粒度权限控制

4. **UI集成**
   - 与 Amis/Antd 后台框架无缝集成
   - 支持图片上传和选择
   - 支持列表排序和过滤

## 使用示例

1. 创建排行榜：
```php
$rankingList = new UserRankingList();
$rankingList->setTitle('热门用户排行');
$rankingList->setScoreSql('SELECT user_id, count(*) as score FROM user_actions GROUP BY user_id');
$rankingList->setCount(100);
```

2. 添加固定排名：
```php
$rankingItem = new UserRankingItem();
$rankingItem->setList($rankingList);
$rankingItem->setNumber(1);
$rankingItem->setUserId('123');
$rankingItem->setFixed(true);
$rankingItem->setTextReason('年度最佳用户');
```

## 注意事项

1. 计算SQL需要返回两列：用户ID和分数
2. 固定排名会覆盖自动计算的结果
3. 排行榜位置用于在不同场景下展示排行榜
4. 所有图片字段建议使用CDN地址

## 命令行工具

### 计算排名

使用以下命令计算用户排名：

```bash
# 计算所有排行榜
php bin/console user-ranking:calculate

# 计算指定排行榜
php bin/console user-ranking:calculate --list-id=123456

# 空运行模式（不更新数据）
php bin/console user-ranking:calculate --dry-run
```

计算过程会：
1. 检查每个排行榜的更新频率限制
2. 执行排行榜配置的SQL获取用户分数
3. 保持固定排名的记录不变
4. 按分数对其他用户进行排序
5. 自动跳过被固定排名占用的名次
6. 遵守排行榜设置的总名次限制
