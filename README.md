# 検討スペシャル

企画・計画・意思決定を、一問ずつ徹底的に検討するCodex・Claude Code用プラグインです。

起動用スキルと、株式会社コアが公開する読み取り専用MCPを組み合わせています。検討手順は利用時にMCPから取得するため、サーバー側の更新後は再インストールせず最新版を利用できます。

## Codexへのインストール

Codexのチャットに、次の文章を貼り付けてください。

```text
core-jp/kento-specialをマーケットプレイス登録して、検討スペシャルをインストールして
```

### うまくいかない場合

Codexのターミナルで、次のコマンドを順番に実行してください。

```powershell
codex plugin marketplace add core-jp/kento-special
codex plugin add kento-special@core-jp
```

## Claude Codeへのインストール

Claude Codeで、次のコマンドを順番に実行してください。

```text
/plugin marketplace add core-jp/kento-special
/plugin install kento-special@core-jp
/reload-plugins
```

## 使い方

企画や計画を伝える依頼文に「検討スペシャル」と加えてください。

```text
新しいサービスを作りたい。検討スペシャルで
```

Claude Codeで明示的に起動する場合は、次の短縮コマンドも利用できます。

```text
/kento-special:start 新しいサービスを検討して
```

## 構成

- `.agents/plugins/marketplace.json`: Codex用マーケットプレイス
- `.claude-plugin/marketplace.json`: Claude Code用マーケットプレイス
- `plugins/kento-special/`: Codex・Claude Code共用プラグイン本体
- `server/`: ヘテムルへ配置するPHP製MCP

## 検討手順の更新

`server/rules/kento-special.md` と `server/rules/version.txt` を更新し、公開サーバーへ反映します。プラグインの再配布は不要です。

## 公開MCP

`https://core1997.com/ai-mcp/kento-special/mcp/`

認証なし・読み取り専用です。利用者の会話内容や個人情報をMCPへ送信する設計にはしていません。

## ライセンス

MIT License
