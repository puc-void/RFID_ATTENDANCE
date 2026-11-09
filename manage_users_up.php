<div class="table-responsive" style="max-height: 620px;"> 
  <table class="table" id="manageUsersTable">
    <thead>
      <tr>
        <th>Card UID (Click to Select)</th>
        <th>User Details</th>
        <th>Serial No</th>
        <th>Status</th>
        <th>Department</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
    <?php
      require 'connectDB.php';

      $sql = "SELECT * FROM users ORDER BY card_select DESC, id DESC";
      $result = mysqli_stmt_init($conn);
      if (!mysqli_stmt_prepare($result, $sql)) {
          echo '<tr><td colspan="6" style="text-align:center;padding:32px;color:var(--danger);">SQL Error encountered</td></tr>';
      } else {
          mysqli_stmt_execute($result);
          $resultl = mysqli_stmt_get_result($result);
          if (mysqli_num_rows($resultl) > 0) {
              while ($row = mysqli_fetch_assoc($resultl)) {
                  $uname = ($row['username'] && $row['username'] != 'None') ? $row['username'] : 'Unassigned Card';
                  $initial = strtoupper(substr($uname, 0, 1));
                  $gender_class = strtolower($row['gender']) === 'female' ? 'female' : 'male';
                  $row_class = ($row['card_select'] == 1) ? 'class="selected-row"' : '';
                  $is_enrolled = ($row['add_card'] == 1);
      ?>
                  <tr <?php echo $row_class; ?>>
                    <td>
                      <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" class="select_btn" id="<?php echo htmlspecialchars($row['card_uid']); ?>" title="Select card <?php echo htmlspecialchars($row['card_uid']); ?>">
                          <?php if ($row['card_select'] == 1): ?>
                            <i class="fa fa-check-circle" style="color:var(--primary);"></i>
                          <?php else: ?>
                            <i class="fa fa-id-card-o" style="opacity:0.65;"></i>
                          <?php endif; ?>
                          <?php echo htmlspecialchars($row['card_uid']); ?>
                        </button>
                      </div>
                    </td>
                    <td>
                      <div class="user-id-badge">
                        <div class="user-avatar-bubble"><?php echo $initial; ?></div>
                        <div>
                          <div style="font-weight: 600; color: var(--text-foreground);"><?php echo htmlspecialchars($uname); ?></div>
                          <div style="font-size: 11.5px; color: var(--text-muted);">
                            <?php echo ($row['gender'] != 'None') ? htmlspecialchars($row['gender']) : 'Pending details'; ?>
                            <?php if (!empty($row['phone']) && $row['phone'] != 'None') echo ' &middot; ' . htmlspecialchars($row['phone']); ?>
                            <?php if (!empty($row['email']) && $row['email'] != 'None') echo ' &middot; ' . htmlspecialchars($row['email']); ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span style="font-weight: 500; color: var(--text-foreground);">
                        <?php echo ($row['serialnumber'] != 0) ? htmlspecialchars($row['serialnumber']) : '---'; ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($is_enrolled): ?>
                        <span class="shadcn-badge success"><i class="fa fa-check"></i> Enrolled</span>
                      <?php else: ?>
                        <span class="shadcn-badge" style="background:var(--warning-soft);color:var(--warning-text);border:1px solid var(--warning-border);">
                          <i class="fa fa-clock-o"></i> New Scan
                        </span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="dept-badge">
                        <?php echo ($row['device_dep'] == "0" || empty($row['device_dep'])) ? "All" : htmlspecialchars($row['device_dep']); ?>
                      </span>
                    </td>
                    <td style="color: var(--text-muted); font-size: 12.5px;"><?php echo htmlspecialchars($row['user_date']); ?></td>
                  </tr>
      <?php
              }
          } else {
              echo '<tr><td colspan="6" class="empty-state" style="text-align:center; padding:48px 20px; color:var(--text-muted);">No cards found in database. Scan a card on an ESP32 reader in Enrollment Mode.</td></tr>';
          }
      }
    ?>
    </tbody>
  </table>
</div>
