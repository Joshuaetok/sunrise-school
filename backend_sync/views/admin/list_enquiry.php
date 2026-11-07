<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-info">
            <div class="panel-heading"><i class="fa fa-list"></i>&nbsp; <?php echo get_phrase('list_enquiry');?></div>
            <div class="panel-wrapper collapse in" aria-expanded="true">
                <div class="panel-body table-responsive">
                    <table id="example23" class="display nowrap" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Purpose</th>
                                <th>Who to See</th>
                                <th>Content</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                                <th style="text-align: center; min-width: 140px;">Respond / Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($select_enquiry as $key => $row): ?>
                            <?php
                                // Clean phone number for WhatsApp
                                $raw_phone = trim($row['mobile']);
                                $clean_phone = preg_replace('/[^0-9]/', '', $raw_phone);
                                if (substr($clean_phone, 0, 1) === '0') {
                                    $clean_phone = '234' . substr($clean_phone, 1);
                                }
                                $is_valid_phone = (!empty($clean_phone) && strlen($clean_phone) >= 10 && $raw_phone !== 'N/A');
                                $wa_greeting = urlencode("Hello " . $row['name'] . ", this is Seeds of Knowledge Intl School Admissions Office responding to your inquiry regarding: " . $row['purpose'] . ".");

                                // Email mailto prefill
                                $email_subject = urlencode("Re: Seeds of Knowledge Intl School Inquiry - " . $row['purpose']);
                                $email_body = urlencode("Dear " . $row['name'] . ",\n\nThank you for contacting Seeds of Knowledge Intl School regarding your inquiry (" . $row['purpose'] . ").\n\n\nKind regards,\nAdmissions & Administration Office\nSeeds of Knowledge Intl School\n12 Sunrise Avenue, Lagos\n+234 800 000 0000\ninfo@seedsofknowledgeschool.com");

                                // Status styling
                                $status = isset($row['status']) ? strtolower($row['status']) : 'new';
                                $is_responded = ($status === 'responded');
                            ?>
                            <tr>
                                <td><span class="label label-info"><?php echo htmlspecialchars($row['category']);?></span></td>
                                <td><strong><?php echo htmlspecialchars($row['name']);?></strong></td>
                                <td>
                                    <?php if ($is_valid_phone): ?>
                                        <a href="tel:<?php echo htmlspecialchars($row['mobile']);?>"><?php echo htmlspecialchars($row['mobile']);?></a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($row['mobile']);?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($row['email']);?>"><?php echo htmlspecialchars($row['email']);?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['purpose']);?></td>
                                <td><?php echo htmlspecialchars($row['whom_to_meet']);?></td>
                                <td style="max-width: 250px; white-space: normal;"><?php echo nl2br(htmlspecialchars($row['content']));?></td>
                                <td>
                                    <?php if ($is_responded): ?>
                                        <span class="label label-success" style="font-size: 11px;"><i class="fa fa-check"></i> Responded</span>
                                    <?php else: ?>
                                        <span class="label label-warning" style="font-size: 11px;"><i class="fa fa-clock-o"></i> New</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['date']);?></td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <?php if ($is_valid_phone): ?>
                                        <!-- WhatsApp Respond -->
                                        <a href="https://wa.me/<?php echo $clean_phone; ?>?text=<?php echo $wa_greeting; ?>" target="_blank" class="btn btn-success btn-circle btn-xs" style="color:white; margin-right: 3px;" title="Reply on WhatsApp">
                                            <i class="fa fa-whatsapp"></i>
                                        </a>
                                        <!-- Direct Phone Call -->
                                        <a href="tel:<?php echo htmlspecialchars($row['mobile']); ?>" class="btn btn-info btn-circle btn-xs" style="color:white; margin-right: 3px;" title="Call Phone">
                                            <i class="fa fa-phone"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($row['email'])): ?>
                                        <!-- Email Respond -->
                                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>?subject=<?php echo $email_subject; ?>&body=<?php echo $email_body; ?>" class="btn btn-primary btn-circle btn-xs" style="color:white; margin-right: 3px;" title="Reply via Email">
                                            <i class="fa fa-envelope"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Status Toggle -->
                                    <?php if (!$is_responded): ?>
                                        <a href="<?php echo base_url();?>admin/list_enquiry/mark_responded/<?php echo $row['enquiry_id'];?>" class="btn btn-warning btn-circle btn-xs" style="color:white; margin-right: 3px;" title="Mark as Responded">
                                            <i class="fa fa-check"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo base_url();?>admin/list_enquiry/mark_new/<?php echo $row['enquiry_id'];?>" class="btn btn-default btn-circle btn-xs" style="margin-right: 3px;" title="Mark as New">
                                            <i class="fa fa-undo"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Delete Button -->
                                    <a href="<?php echo base_url();?>admin/list_enquiry/delete/<?php echo $row['enquiry_id'];?>" onclick="return confirm('Are you sure you want to delete this inquiry?');" class="btn btn-danger btn-circle btn-xs" style="color:white" title="Delete Inquiry">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
