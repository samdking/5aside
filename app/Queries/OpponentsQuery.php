<?php

namespace App\Queries;


class OpponentsQuery
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
  opponents.id,
  opponents.first_name,
  opponents.last_name,
  COUNT(*) AS apps,
  MAX(matches.date) AS `last_app`,
  SUM(teams.winners) AS wins,
  SUM(teams.draw) AS draws,
  SUM(opp_teams.winners) AS losses,
  SUM(teams.scored) AS `goals_for`,
  SUM(opp_teams.scored) AS `goals_against`,
  SUM(teams.scored) - SUM(opp_teams.scored) AS diff,
  SUM(teams.winners) * 3 + SUM(teams.draw) AS pts,
  ROUND((SUM(IF(teams.winners, 1, 0)) / COUNT(*) * 100), 1) AS win_percentage,
  SUM(IF(teams.winners AND teams.handicap, 1, 0)) AS handicap_wins,
  SUM(IF(teams.draw = 0 AND teams.winners = 0 AND teams.handicap, 1, 0)) AS handicap_losses,
  SUM(IF(teams.handicap, 1, 0)) AS handicap_apps
FROM teams
JOIN player_team ON teams.id = player_team.team_id
JOIN matches ON matches.id = teams.match_id
JOIN teams AS opp_teams ON opp_teams.match_id = teams.match_id AND opp_teams.id != teams.id
JOIN player_team opp_player_team ON opp_player_team.team_id = opp_teams.id
JOIN players opponents ON opponents.id = opp_player_team.player_id
WHERE player_team.player_id = ? AND matches.date >= ? AND matches.date <= ? AND opp_player_team.injured = 0 AND player_team.injured = 0 AND is_void = 0
GROUP BY opponents.id
ORDER BY `pts` DESC, `diff` DESC, `win_percentage` DESC, `handicap_wins` DESC, `apps` DESC, `losses` ASC, `last_app` DESC, opponents.last_name ASC
SQL;

    $placeholders = array_values(array_filter([
      $this->request->player,
      (new Filters\FromDate)->get($this->request),
      (new Filters\ToDate)->get($this->request),
    ]));

    $form = $this->form->getForPlayer((object)(['id' => $this->request->player]));

    return collect(\DB::select($query, $placeholders))->each(function($player) use ($form) {
      $player->form = $form->map(function($match) use ($player) {
        return $match && $match->opponents->map->id->contains($player->id) ? $match : null;
      });
    });
  }
}
