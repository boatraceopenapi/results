# レスポンススキーマ（結果）

> ⚠️ 本 API は非推奨です。新しく使う場合は後継の [boatraceopenapi/api](https://github.com/boatraceopenapi/api) を利用してください。

## エンドポイント

| バージョン | URL | 対応期間 |
|---|---|---|
| v3 | `https://boatraceopenapi.github.io/results/v3/YYYY/YYYYMMDD.json` | 2016年08月01日以降 |
| v2 | `https://boatraceopenapi.github.io/results/v2/YYYY/YYYYMMDD.json` | 2016年10月01日以降 |

- 日付は日本標準時（JST）基準です。データがない日付は HTTP 404 を返します。
- `today.json`（当日）と `yesterday.json`（前日）もあります（例: `/results/v3/today.json`）。
- 約 3 時間ごとに当日分と前日分を取得し直して上書きします。
- 出走表は [programs](https://github.com/boatraceopenapi/programs)、直前情報は [previews](https://github.com/boatraceopenapi/previews) で配信しています。3 つは同じ日付・同じレースの並びで対応しています。

---

## JSON 全体構造（v3）

```
{
  "results": [
    {
      "date": "2026-05-01",
      "stadium_number": 1,
      "number": 1,
      ...
      "boats": [
        { "racer_boat_number": 1, ... },
        ...
      ],
      "payouts": {
        "trifecta": [{ "combination": "1-2-3", "amount": 1230 }],
        ...
      }
    },
    ...
  ]
}
```

- `results` はその日の全レースの配列です。
- `boats` は出走艇の配列で、`racer_boat_number`（枠番）`1`〜`6` の 6 要素です。着順の順ではなく枠番の順です。
- レースのキーは `(date, stadium_number, number)`、出走艇のキーはそれに `racer_boat_number` を加えたものです。

---

## レース

| フィールド | 型 | 説明 |
|---|---|---|
| `date` | string | 開催日（`YYYY-MM-DD`） |
| `stadium_number` | int | レース場（`1`〜`24`） |
| `number` | int | レース番号（`1`〜`12`） |
| `wind_speed` | int | 風速（m） |
| `wind_direction_number` | int | 風向（`1`〜`17`） |
| `wave_height` | int | 波高（cm） |
| `weather_number` | int | 天候 |
| `air_temperature` | float | 気温（℃） |
| `water_temperature` | float | 水温（℃） |
| `technique_number` | int | 決まり手 |
| `payouts` | object | 払戻（下記） |

## 出走艇（`boats[]`）

| フィールド | 型 | 説明 |
|---|---|---|
| `racer_boat_number` | int | 枠番（`1`〜`6`） |
| `racer_course_number` | int | 進入コース（`1`〜`6`）。枠番と一致するとは限りません |
| `racer_start_timing` | float | スタートタイミング。フライングは負の値、出遅れ・欠場は `null` |
| `racer_place_number` | int | 着順。`1`〜`6` が着順で、`7` 以上は失格・欠場など |
| `racer_number` | int | 登録番号 |
| `racer_name` | string | 選手名 |

## 払戻（`payouts`）

キーは常に 7 種類あり、それぞれ `{ "combination": string, "amount": int }` の配列です。

| キー | 券種 | `combination` の例 |
|---|---|---|
| `trifecta` | 3 連単 | `"1-2-3"` |
| `trio` | 3 連複 | `"1=2=3"` |
| `exacta` | 2 連単 | `"1-2"` |
| `quinella` | 2 連複 | `"1=2"` |
| `quinella_place` | 拡連複 | `"1=2"` |
| `win` | 単勝 | `"1"` |
| `place` | 複勝 | `"1"` |

- `-` は着順どおり、`=` は順不同の組み合わせです。
- `amount` は 100 円あたりの払戻金（円）です。
- 通常は `quinella_place` が 3 要素、`place` が 2 要素、そのほかが 1 要素です。同着があると要素が増えます。
- 中止・不成立のレースは配列が空です。

---

## コード値

`stadium_number`・`wind_direction_number`・`weather_number`・`technique_number`・`racer_place_number` の番号は、後継 API と同じ意味です。対応表は後継 API の [コード表](https://boatraceopenapi.github.io/api/v1/codes.md) を参照してください（`racer_place_number` は後継 API の `place_number`）。

- `racer_place_number` を数値として平均・並べ替えしないでください。`14` は「14 着」ではなくフライングです。着順として扱うのは `1`〜`6` だけにしてください。
- `wind_direction_number` の「北」「東」などは地図上の方角ではなく、スタートラインに対する相対的な向きです。`3`〜`7` が追い風、`11`〜`15` が向かい風、`17` が無風です。

---

## データの注意点

- 中止になったレースは、`racer_place_number` がすべて `null`、気象と `technique_number` が `null`、払戻の配列が空です。集計から除外してください。
- `amount` が `0` の要素があります（主に `place` と `quinella_place`）。実際の払戻金ではない可能性があるため、回収率などの集計では注意してください。
- 当日のファイルは途中の状態です。確定した過去データとして使うのは、翌々日以降に取得したファイルにしてください。

---

## v2 との違い

出走艇（`boats[]`）のフィールドは v3 と同じです。レースのフィールドと払戻は次のとおり名前が違います。

| v3 | v2 |
|---|---|
| `date` | `race_date` |
| `stadium_number` | `race_stadium_number` |
| `number` | `race_number` |
| `wind_speed` | `race_wind` |
| `wind_direction_number` | `race_wind_direction_number` |
| `wave_height` | `race_wave` |
| `weather_number` | `race_weather_number` |
| `air_temperature` | `race_temperature` |
| `water_temperature` | `race_water_temperature` |
| `technique_number` | `race_technique_number` |
| `payouts.*[].amount` | `payouts.*[].payout` |
