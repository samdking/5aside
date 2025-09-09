<?php

namespace App\Queries;

abstract class CompetitorQuery
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
    $join = $this->joinWith;

    $query = <<<SQL
  SELECT
    players.*,
    COUNT(*) AS `apps`,
    MAX(matches.date) AS `last_app`,
    SUM(teams.winners) AS `wins`,
    SUM(teams.draw) AS `draws`,
    SUM(opp_teams.winners) AS `losses`,
    SUM(teams.scored) AS `goals_for`,
    SUM(opp_teams.scored) AS `goals_against`,
    SUM(teams.scored) - SUM(opp_teams.scored) AS `diff`,
    SUM(teams.winners) * 3 + SUM(teams.draw) AS `pts`,
    ROUND(SUM(teams.winners) / COUNT(*) * 100, 2) AS `win_percentage`,
    SUM(IF(teams.winners AND teams.handicap, 1, 0)) AS `handicap_wins`,
    SUM(IF(opp_teams.winners AND teams.handicap, 1, 0)) AS `handicap_losses`,
    SUM(IF(teams.handicap, 1, 0)) AS `handicap_apps`
  FROM teams
  JOIN player_team ON teams.id = player_team.team_id
  JOIN matches ON matches.id = teams.match_id
  JOIN teams AS opp_teams ON opp_teams.match_id = teams.match_id AND opp_teams.id != teams.id
  JOIN player_team competitor ON competitor.team_id = ${join}.id AND competitor.player_id != player_team.player_id
  JOIN players ON players.id = competitor.player_id
  WHERE
    player_team.player_id = ? AND
    matches.date >= ? AND matches.date <= ? AND
    matches.is_void = 0 AND
    competitor.injured = 0 AND
    player_team.injured = 0
  GROUP BY players.id
  ORDER BY `pts` DESC, `diff` DESC, `win_percentage` DESC, `handicap_wins` DESC, `apps` DESC, `losses` ASC, `last_app` DESC, players.last_name ASC
SQL;

    $placeholders = array_values(array_filter([
      $this->request->player,
      (new Filters\FromDate)->get($this->request),
      (new Filters\ToDate)->get($this->request),
    ]));

    $form = $this->form->getForPlayer((object)(['id' => $this->request->player]));

    return collect(\DB::select($query, $placeholders))->each(function($player) use ($form) {
      $player->form = $this->formWithCompetitor($form, $player);
    });
  }

  protected abstract function formWithCompetitor($form, $player);
}
