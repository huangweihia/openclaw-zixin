# OpenClaw 智信（Laravel + Vue）

> **本目录即 Laravel 应用根**（含 `docker/`、`docker-compose.yml`）。  
> **Git 仓库建议在本目录执行 `git init`**；服务器部署时也可直接同步本目录内容，无需再改挂载路径。
>
> **把“内容站”升级成“可变现的增长系统”**：内容分发 → 互动沉淀 → 会员分层 → 支付转化 → 触达复购。

[![Laravel](https://img.shields.io/badge/Laravel-10-ff2d20.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777bb4.svg)](https://www.php.net)
[![Vue](https://img.shields.io/badge/Vue-3-42b883.svg)](https://vuejs.org)
[![Vite](https://img.shields.io/badge/Vite-5-646cff.svg)](https://vitejs.dev)
[![Element Plus](https://img.shields.io/badge/Element%20Plus-Admin-409eff.svg)](https://element-plus.org)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ed.svg)](https://docs.docker.com/compose/)

OpenClaw 智信是一个面向“**内容变现 + 私域运营 + 会员增长**”的一体化站点：前台承载内容分发与转化，后台提供内容运营、审核、触达与数据闭环能力。

## ✨ 亮点（更像产品，而不是 Demo）

- **内容 × 会员 × 运营一体化**：不是“发文章”，而是“从流量到付费”的可运营闭环
- **可持续增长工具箱**：UGC 投稿、评论、收藏、审核、举报、积分、订阅、推送、邮件
- **漂亮且可扩展的管理端**：Vue + Element Plus，支持菜单/按钮级权限（RBAC）
- **可观测性**：OpenClaw 任务日志对外上报 + 管理端检索筛选 + 统计图表

## 👥 适用人群

- **内容型/社区型产品**：AI 资讯、工具导航、教程/课程、案例库、资源站
- **私域运营团队**：需要 SOP、触达、订阅/会员、用户增长工具链
- **创业团队**：用一套可落地的“内容 + 会员 + 运营后台”快速验证商业模式

## 🧩 核心业务模块（你能卖什么）

| 模块 | 能带来的价值 |
| --- | --- |
| 内容供给（文章/案例/工具/项目/付费资源） | 构建 SEO 与持续流量入口，承接转化 |
| 会员体系（VIP/SVIP） | 权益分层与付费门槛，提升 ARPU |
| 支付与订阅 | 订单闭环，支持长期订阅模型 |
| 互动与 UGC（点赞/收藏/评论/投稿） | 提升留存与内容供给效率 |
| 审核与举报 | 保障社区质量与增长安全 |
| 运营与触达（站内信/邮件/推送） | 促活、召回、复购 |
| 站点配置（Logo/名称/口号等） | 快速搭建品牌化站点 |
| OpenClaw 任务日志 + 图表 | 任务运行可观测，便于排障与运营复盘 |

## 🔁 业务闭环（可直接拿去讲）

```mermaid
flowchart LR
  A[内容分发\n文章/案例/工具] --> B[互动沉淀\n评论/收藏/投稿]
  B --> C[权益分层\nVIP/SVIP]
  C --> D[支付转化\n订单/订阅]
  D --> E[触达复购\n站内信/邮件/推送]
  E --> A
```

## 🖼️ 截图 / 演示（放图就能更吸引人）

- `docs/screenshots/home.png`：首页
- `docs/screenshots/admin-dashboard.png`：后台总览
- `docs/screenshots/admin-rbac.png`：权限与菜单
- `docs/screenshots/openclaw-task-logs.png`：任务日志 + 图表

（先占位，后续你补几张图，这个 README 就“像一个真产品”了）

## 📚 项目文档（同仓库内置）

项目文档在本目录下的 `docs/`：

- 开发环境配置：`docs/01-开发环境配置.md`
- 数据库设计：`docs/02-数据库表字段详细设计.md`
- 其他：`docs/` 下的「功能清单 / 原型图 / OpenClaw 说明」等

## 技术栈

- **后端**：Laravel 10（PHP 8.2+）
- **管理端**：Vue 3 + Vite + Element Plus
- **样式**：Tailwind（CDN）+ 皮肤变量（`public/css/skins.css`）
- **数据库**：MySQL 8
- **缓存/Session（推荐）**：Redis

## 快速开始（Windows + Docker，推荐）

先进入本目录（与 `docker-compose.yml` 同级）：

```bash
cd D:\lewan\openclaw-data\workspace\openclaw_zhixin\_laravel_temp
```

准备 `.env` 并启动：

```bash
copy .env.example .env
docker compose up -d

docker compose exec -T php composer install
docker compose exec -T php npm install

docker compose exec -T php php artisan key:generate
docker compose exec -T php php artisan migrate
docker compose exec -T php php artisan db:seed

docker compose exec -T php npm run build
```

访问：

- **前台**：`http://localhost:8083`
- **后台**：`http://localhost:8083/admin`

## 常用命令

```bash
docker compose exec -T php php artisan optimize:clear
docker compose exec -T php php artisan migrate
docker compose exec -T php php artisan db:seed
docker compose exec -T php npm run dev
docker compose exec -T php npm run build
```

## 初始化管理员账号（Seeder）

`db:seed` 会写入默认管理账号（用于首次登录）：

- **账号**：`dahu@openclaw.test`
- **密码**：`mqq123456`

生产环境务必第一时间修改口令/替换默认账号策略。

## 常见问题

### 1）PHP 容器启动报 `ln: failed to create symbolic link 'public/storage': No such file or directory`

已在 `docker/php/docker-entrypoint.sh` 做了兼容：启动时自动创建 `public` 与 `storage/app/public` 目录再创建软链。

### 2）上传文件 413（Request Entity Too Large）

需要同时调整 Nginx（`client_max_body_size`）与 PHP（`upload_max_filesize` / `post_max_size`）。  
详见：`docs/01-开发环境配置.md` 中「上传尺寸限制（Nginx / PHP）」章节。

## 服务器部署（与开发同一套路径）

将本目录整体同步到服务器（例如 `/opt/zixin/openclaw-zixin`），保证 **`artisan`、`docker/`、`docker-compose.server.yml` 在同一层**，然后：

```bash
cd /opt/zixin/openclaw-zixin
docker compose -f docker-compose.server.yml up -d --build
```

访问：`http://服务器IP/`（默认映射 **80:80**；开发环境 `docker-compose.yml` 为 **8083:80**）。

# OpenClaw 鏅轰俊锛圠aravel + Vue锛?
> **鏈洰褰曞嵆 Laravel 搴旂敤鏍?*锛堝惈 `docker/`銆乣docker-compose.yml`锛夈€侴it 浠撳簱寤鸿浠ユ湰鐩綍涓烘牴鍒濆鍖栵紝鏈嶅姟鍣ㄩ儴缃叉椂涔熷彲鐩存帴鍚屾鏈洰褰曞唴瀹癸紝鏃犻渶鍐嶆敼鎸傝浇璺緞銆?
> **鎶娾€滃唴瀹圭珯鈥濆崌绾ф垚鈥滃彲鍙樼幇鐨勫闀跨郴缁熲€?*锛氬唴瀹瑰垎鍙?鈫?浜掑姩娌夋穩 鈫?浼氬憳鍒嗗眰 鈫?鏀粯杞寲 鈫?瑙﹁揪澶嶈喘銆?
[![Laravel](https://img.shields.io/badge/Laravel-10-ff2d20.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777bb4.svg)](https://www.php.net)
[![Vue](https://img.shields.io/badge/Vue-3-42b883.svg)](https://vuejs.org)
[![Vite](https://img.shields.io/badge/Vite-5-646cff.svg)](https://vitejs.dev)
[![Element Plus](https://img.shields.io/badge/Element%20Plus-Admin-409eff.svg)](https://element-plus.org)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ed.svg)](https://docs.docker.com/compose/)

OpenClaw 鏅轰俊鏄竴涓潰鍚戔€?*鍐呭鍙樼幇 + 绉佸煙杩愯惀 + 浼氬憳澧為暱**鈥濈殑涓€浣撳寲绔欑偣锛氬墠鍙版壙杞藉唴瀹瑰垎鍙戜笌杞寲锛屽悗鍙版彁渚涘唴瀹硅繍钀ャ€佸鏍搞€佽Е杈句笌鏁版嵁闂幆鑳藉姏銆?
## 鉁?浜偣锛堟洿鍍忎骇鍝侊紝鑰屼笉鏄?Demo锛?
- **鍐呭 脳 浼氬憳 脳 杩愯惀涓€浣撳寲**锛氫笉鏄€滃彂鏂囩珷鈥濓紝鑰屾槸鈥滀粠娴侀噺鍒颁粯璐光€濈殑鍙繍钀ラ棴鐜?- **鍙寔缁闀垮伐鍏风**锛歎GC 鎶曠銆佽瘎璁恒€佹敹钘忋€佸鏍搞€佷妇鎶ャ€佺Н鍒嗐€佽闃呫€佹帹閫併€侀偖浠?- **婕備寒涓斿彲鎵╁睍鐨勭鐞嗙**锛歏ue + Element Plus锛屾敮鎸佽彍鍗?鎸夐挳绾ф潈闄愶紙RBAC锛?- **鍙娴嬫€?*锛歄penClaw 浠诲姟鏃ュ織瀵瑰涓婃姤 + 绠＄悊绔绱㈢瓫閫?+ 缁熻鍥捐〃

## 馃懃 閫傜敤浜虹兢

- **鍐呭鍨嬩骇鍝?绀惧尯鍨嬩骇鍝?*锛欰I 璧勮銆佸伐鍏峰鑸€佹暀绋?璇剧▼銆佹渚嬪簱銆佽祫婧愮珯
- **绉佸煙杩愯惀鍥㈤槦**锛氶渶瑕?SOP銆佽Е杈俱€佽闃?浼氬憳銆佺敤鎴峰闀垮伐鍏烽摼
- **鍒涗笟鍥㈤槦**锛氬笇鏈涚敤涓€濂楀彲钀藉湴鐨勨€滃唴瀹?+ 浼氬憳 + 杩愯惀鍚庡彴鈥濆揩閫熼獙璇佸晢涓氭ā寮?
## 馃З 鏍稿績涓氬姟妯″潡锛堜綘鑳藉崠浠€涔堬級

| 妯″潡 | 鑳藉甫鏉ョ殑浠峰€?|
| --- | --- |
| 鍐呭渚涚粰锛堟枃绔?妗堜緥/宸ュ叿/椤圭洰/浠樿垂璧勬簮锛?| 鏋勫缓 SEO 涓庢寔缁祦閲忓叆鍙ｏ紝鎵挎帴杞寲 |
| 浼氬憳浣撶郴锛圴IP/SVIP锛?| 鏉冪泭鍒嗗眰涓庝粯璐归棬妲涳紝鎻愬崌 ARPU |
| 鏀粯涓庤闃?| 璁㈠崟闂幆锛屾敮鎸侀暱鏈熻闃呮ā鍨?|
| 浜掑姩涓?UGC锛堢偣璧?鏀惰棌/璇勮/鎶曠锛?| 鎻愬崌鐣欏瓨涓庡唴瀹逛緵缁欐晥鐜?|
| 瀹℃牳涓庝妇鎶?| 淇濋殰绀惧尯璐ㄩ噺涓庡闀垮畨鍏?|
| 杩愯惀涓庤Е杈撅紙绔欏唴淇?閭欢/鎺ㄩ€侊級 | 淇冩椿銆佸彫鍥炪€佸璐?|
| 绔欑偣閰嶇疆锛圠ogo/鍚嶇О/鍙ｅ彿绛夛級 | 蹇€熸惌寤哄搧鐗屽寲绔欑偣 |
| OpenClaw 浠诲姟鏃ュ織 + 鍥捐〃 | 浠诲姟杩愯鍙娴嬶紝渚夸簬鎺掗殰涓庤繍钀ュ鐩?|

## 馃攣 涓氬姟闂幆锛堝彲鐩存帴鎷垮幓璁诧級

```mermaid
flowchart LR
  A[鍐呭鍒嗗彂\n鏂囩珷/妗堜緥/宸ュ叿] --> B[浜掑姩娌夋穩\n璇勮/鏀惰棌/鎶曠]
  B --> C[鏉冪泭鍒嗗眰\nVIP/SVIP]
  C --> D[鏀粯杞寲\n璁㈠崟/璁㈤槄]
  D --> E[瑙﹁揪澶嶈喘\n绔欏唴淇?閭欢/鎺ㄩ€乚
  E --> A
```

## 馃柤锔?鎴浘 / 婕旂ず锛堟斁鍥惧氨鑳芥洿鍚稿紩浜猴級

- `docs/screenshots/home.png`锛氶椤?- `docs/screenshots/admin-dashboard.png`锛氬悗鍙版€昏
- `docs/screenshots/admin-rbac.png`锛氭潈闄愪笌鑿滃崟
- `docs/screenshots/openclaw-task-logs.png`锛氫换鍔℃棩蹇?+ 鍥捐〃

锛堝厛鍗犱綅锛屽悗缁綘琛ュ嚑寮犲浘锛岃繖涓?README 灏扁€滃儚涓€涓湡浜у搧鈥濅簡锛?
## 馃摎 椤圭洰鏂囨。锛堝悓浠撳簱鍐呯疆锛?
椤圭洰鏂囨。宸插悎骞跺埌鏈洰褰曚笅鐨?`docs/`锛?
- 寮€鍙戠幆澧冮厤缃細`docs/01-寮€鍙戠幆澧冮厤缃?md`
- 鏁版嵁搴撹璁★細`docs/02-鏁版嵁搴撹〃瀛楁璇︾粏璁捐.md`
- 鍏朵粬锛歚docs/` 涓嬬殑銆屽姛鑳芥竻鍗?/ 鍘熷瀷鍥?/ OpenClaw 璇存槑銆嶇瓑

## 鎶€鏈爤

- **鍚庣**锛歀aravel 10锛圥HP 8.2+锛?- **绠＄悊绔?*锛歏ue 3 + Vite + Element Plus
- **鏍峰紡**锛歍ailwind锛圕DN锛? 鐨偆鍙橀噺锛坄public/css/skins.css`锛?- **鏁版嵁搴?*锛歁ySQL 8
- **缂撳瓨/Session锛堟帹鑽愶級**锛歊edis

## 蹇€熷紑濮嬶紙Windows + Docker锛屾帹鑽愶級

**鍏堣繘鍏ヤ粨搴撴牴鐩綍**锛堝嵆 Laravel 鏍癸紝涓?`docker-compose.yml` 鍚岀骇锛夛紝渚嬪锛?
```bash
# Windows 绀轰緥
cd D:\lewan\openclaw-data\workspace\openclaw_zhixin\_laravel_temp
```

```bash
docker compose up -d
docker compose exec -T php composer install
docker compose exec -T php npm install
docker compose exec -T php php artisan key:generate
docker compose exec -T php php artisan migrate
docker compose exec -T php php artisan db:seed
docker compose exec -T php npm run build
```

璁块棶锛?
- **鍓嶅彴**锛歚http://localhost:8083`
- **鍚庡彴**锛氶粯璁ゅ悓鍩燂紙鎴栨寜 Nginx 閰嶇疆浣跨敤 `admin.localhost`锛?
## 甯哥敤鍛戒护

```bash
docker compose exec -T php php artisan optimize:clear
docker compose exec -T php php artisan migrate
docker compose exec -T php php artisan db:seed
docker compose exec -T php npm run dev
docker compose exec -T php npm run build
```

## 鍒濆鍖栫鐞嗗憳璐﹀彿锛圫eeder锛?
`db:seed` 浼氬啓鍏ラ粯璁ょ鐞嗚处鍙凤紙鐢ㄤ簬棣栨鐧诲綍锛夛細

- **璐﹀彿**锛歚dahu@openclaw.test`
- **瀵嗙爜**锛歚mqq123456`

鎻愮ず锛氱敓浜х幆澧冨姟蹇呯涓€鏃堕棿淇敼鍙ｄ护/鏇挎崲榛樿璐﹀彿绛栫暐銆?
## 甯歌闂

### 1锛塒HP 瀹瑰櫒鍚姩鎶?`ln: failed to create symbolic link 'public/storage': No such file or directory`

鍘熷洜鏄鍣ㄥ叆鍙ｈ剼鏈湪鍒涘缓 `public/storage` 杞摼鏃讹紝`public/` 鐩綍涓嶅瓨鍦ㄤ細鐩存帴澶辫触骞堕€€鍑恒€?
宸插湪 `docker/php/docker-entrypoint.sh` 鍋氫簡鍏煎锛氬惎鍔ㄦ椂鑷姩鍒涘缓 `public` 涓?`storage/app/public` 鐩綍鍐嶅垱寤鸿蒋閾俱€?
### 2锛変笂浼犳枃浠?413锛圧equest Entity Too Large锛?
闇€瑕佸悓鏃惰皟鏁?Nginx锛坄client_max_body_size`锛変笌 PHP锛坄upload_max_filesize` / `post_max_size`锛夈€?璇﹁锛歚docs/01-寮€鍙戠幆澧冮厤缃?md` 涓€屼笂浼犲昂瀵搁檺鍒讹紙Nginx / PHP锛夈€嶇珷鑺傘€?
## 鏈嶅姟鍣ㄩ儴缃诧紙涓庡紑鍙戝悓涓€濂楄矾寰勶級

- 灏嗘湰鐩綍鏁翠綋鍚屾鍒版湇鍔″櫒锛堜緥濡?`/opt/zixin/openclaw-zixin`锛夛紝淇濊瘉 **`artisan`銆乣docker/`銆乣docker-compose.server.yml` 鍦ㄥ悓涓€灞?*銆?- 鐢熶骇鐜浣跨敤锛?
```bash
cd /opt/zixin/openclaw-zixin
docker compose -f docker-compose.server.yml up -d --build
```

- 璁块棶锛歚http://鏈嶅姟鍣↖P/`锛堥粯璁ゆ槧灏?**80:80**锛涘紑鍙戠幆澧?`docker-compose.yml` 涓?**8083:80**锛夈€?
