<div class="container">
  <div class="speedcard">
    <div class="row">
      <div class="col-xs-12">
        <div class="card-icon <?php print $card_suit?>">
          <?php
            print "$card_icon";
          ?>
        </div>
        <p class="lead card-name <?php print $card_suit?>">
          <?php
            print "$card_name";
          ?>
        </p>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-12">
        <p>
          <a class="btn btn-lg btn-success" href="." role="button">
            <?php print t("New card"); ?>
          </a>
        </p>
      </div>
    </div>
  </div>
</div>
