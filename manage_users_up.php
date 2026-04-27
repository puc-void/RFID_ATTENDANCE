<div class="table-responsive" style="max-height: 800px;"> 
  <table class="table">
    <thead>
      <tr>
        <th>ID | Card UID</th>
        <th>User Details</th>
        <th>S.No</th>
        <th>Date</th>
        <th>Department</th>
      </tr>
    </thead>
    <tbody>
    <?php
      //Connect to database
      require 'connectDB.php';

        $sql = "SELECT * FROM users ORDER BY id DESC";
        $result = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($result, $sql)) {
            echo '<tr><td colspan="5" class="error">SQL Error encountered</td></tr>';
        }
        else{
            mysqli_stmt_execute($result);
            $resultl = mysqli_stmt_get_result($result);
          if (mysqli_num_rows($resultl) > 0){
              while ($row = mysqli_fetch_assoc($resultl)){
                  $initial = strtoupper(substr($row['username'] ?? 'U', 0, 1));
                  $gender_class = strtolower($row['gender']) === 'female' ? 'female' : 'male';
                  $selected_class = ($row['card_select'] == 1) ? 'style="background: rgba(0, 240, 255, 0.08);"' : '';
      ?>
                  <tr <?php echo $selected_class;?>>
                    <td>
                      <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="color: #4a5568; font-size: 11px; font-weight: 600; min-width: 20px;"><?php echo $row['id'];?></span>
                        <div style="height: 12px; width: 1px; background: rgba(255,255,255,0.1);"></div>
                        <?php if ($row['card_select'] == 1): ?>
                          <span style="color: #00F0FF;" title="Currently Selected"><i class="fa fa-check-circle"></i></span>
                        <?php endif; ?>
                        <button type="button" class="select_btn" id="<?php echo $row['card_uid'];?>" 
                                style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #00F0FF; 
                                       font-family: 'Courier New', monospace; padding: 4px 8px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                          <?php echo $row['card_uid'];?>
                        </button>
                      </div>
                    </td>
                    <td>
                      <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, rgba(0,240,255,0.1), rgba(189,0,255,0.1)); 
                                    border: 1px solid rgba(0,240,255,0.2); display: flex; align-items: center; justify-content: center; 
                                    font-size: 12px; font-weight: 700; color: #00F0FF; flex-shrink: 0;">
                          <?php echo $initial; ?>
                        </div>
                        <div>
                          <div style="font-weight: 500; color: #f0f4ff;"><?php echo htmlspecialchars($row['username']);?></div>
                          <div style="font-size: 11px; color: #8892aa;">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: <?php echo ($gender_class == 'male' ? '#60a5fa' : '#f472b6');?>; margin-right: 4px;"></span>
                            <?php echo $row['gender'];?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span style="color: #f0f4ff; font-size: 14px; font-weight: 500;">
                        <?php echo ($row['serialnumber'] != 0) ? $row['serialnumber'] : '---'; ?>
                      </span>
                    </td>
                    <td><span style="color: #8892aa; font-size: 13px;"><?php echo $row['user_date'];?></span></td>
                    <td>
                      <span style="display: inline-block; padding: 3px 10px; background: rgba(189, 0, 255, 0.07); border: 1px solid rgba(189, 0, 255, 0.2); 
                                   color: #d866ff; border-radius: 6px; font-size: 12px; font-weight: 500;">
                        <?php echo ($row['device_dep'] == "0") ? "All" : htmlspecialchars($row['device_dep']);?>
                      </span>
                    </td>
                  </tr>
      <?php
              }   
          } else {
              echo '<tr><td colspan="5" style="text-align:center; padding:40px; color:#4a5568;">No users found in database.</td></tr>';
          }
      }
    ?>
    </tbody>
  </table>
</div>