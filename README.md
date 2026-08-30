# 検討スペシャル

企画・計画・意思決定を、一問ずつ徹底的に検討するCodex用プラグインです。

起動用スキルと、株式会社コアが公開する読み取り専用MCPを組み合わせています。検討手順は利用時にMCPから取得するため、サーバー側の更新後は再インストールせず最新版を利用できます。

## インストール

Codexのチャットに、次の文章を貼り付けてください。

```text
core-jp/kento-specialをマーケットプレイス登録して、検討スペシャルをインストールして
```

インストール後、Codexを再起動してください。

### うまくいかない場合

Codexのターミナルで、次のコマンドを順番に実行してください。

```powershell
codex plugin marketplace add core-jp/kento-special
codex plugin add kento-special@core-jp
```

## 使い方

企画や計画を伝える依頼文に「検討スペシャル」と加えてください。

```text
新しいサービスを作りたい。検討スペシャルで
```

## 構成

- `.agents/plugins/marketplace.json`: GitHub配布用マーケットプレイス
- `plugins/kento-special/`: Codexプラグイン本体
- `server/`: ヘテムルへ配置するPHP製MCP

## 検討手順の更新

`server/rules/kento-special.md` と `server/rules/version.txt` を更新し、公開サーバーへ反映します。プラグインの再配布は不要です。

## 公開MCP

`https://core1997.com/ai-mcp/kento-special/mcp/`

認証なし・読み取り専用です。利用者の会話内容や個人情報をMCPへ送信する設計にはしていません。

## ライセンス

MIT License
