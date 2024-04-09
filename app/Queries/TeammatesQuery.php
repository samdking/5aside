<?php

namespace App\Queries;


class TeammatesQuery
{
  protected $request;
  protected $query;

  public function __construct($request, FormQuery $form)
  {
    $this->request = $request;
    $this->form = $form;
  }

  public function get()
  {
    $this->request['form_matches'] = 10;

$query = <<<SQL
    SELECT
    teammates.*,
    COUNT(player.team_id) AS `apps`,
    MAX(player.date) AS `last_app`,
    SUM(player.winners) AS `wins`,
    SUM(player.draw) AS `draws`,
    SUM(player.winners) * 3 + SUM(player.draw) AS pts,
    SUM(player.lose) AS `losses`,
    SUM(player.goals_for) AS `goals_for`,
    SUM(player.goals_against) AS `goals_against`,
    SUM(player.goals_for) - SUM(player.goals_against) AS `diff`,
    ROUND(SUM(player.winners) / COUNT(player.team_id) * 100, 2) AS `win_percentage`,
    SUM(IF(player.winners AND player.handicap, 1, 0)) AS handicap_wins,
    SUM(IF(player.lose AND player.handicap, 1, 0)) AS handicap_losses,
    SUM(IF(player.handicap, 1, 0)) AS handicap_apps
    FROM players AS teammates
    JOIN player_team AS player_teammates ON player_teammates.player_id = teammates.id
    INNER JOIN (
      SELECT
      players.id,
      team_id,
      teams.winners,
      opps.winners AS lose,
      teams.draw,
      teams.scored AS goals_for,
      opps.scored AS goals_against,
      teams.handicap,
      matches.date
      FROM player_team
      JOIN players ON players.id = player_team.player_id
      JOIN teams ON teams.id = player_team.team_id
      JOIN teams AS opps on opps.match_id = teams.match_id AND opps.id != teams.id
      JOIN matches ON matches.id = teams.match_id
      WHERE
        players.id = ? AND
        matches.date >= ? AND matches.date <= ? AND
        matches.is_void = 0 AND
        player_team.injured = 0
      ) AS player
    ON player.team_id = player_teammates.team_id
    WHERE teammates.id != player.id and player_teammates.injured = 0
    GROUP BY teammates.id
    ORDER BY `pts` DESC, `diff` DESC, `win_percentage` DESC, `handicap_wins` DESC, `apps` DESC, `losses` ASC, `last_app` DESC, teammates.last_name ASC
SQL;

    $placeholders = array_values(array_filter([
      $this->request->player,
      (new Filters\FromDate)->get($this->request),
      (new Filters\ToDate)->get($this->request),
    ]));

    $form = $this->form->getForPlayer((object)(['id' => $this->request->player]));

    return collect(\DB::select($query, $placeholders))->each(function($player) use ($form) {
      $player->form = $form->map(function($match) use ($player) {
        return $match && $match->teammates->map->id->contains($player->id) ? $match : null;
      });
    });
  }
}
