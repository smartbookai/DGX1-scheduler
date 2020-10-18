<?php

include(__BASE_PATH__ . '/pages/header.php');
?>

<section class="section container" style="width: 75%;">
    <div class="col s12 m12 offset-m1 center">
        Content of FAQ section
    </div>
    <ul class="collapsible popout">
        <li>
            <div class="collapsible-header"><i class="material-icons">schedule</i>What are the limits on resource requests?</div>
            <div class="collapsible-body"><span>The maximum time is one week (168 hours). The maximum number of GPUs is currently 8.</span></div>
        </li>
        <li>
            <div class="collapsible-header"><i class="material-icons">storage</i>How do I prepare my data to use on the DGX-1?</div>
            <div class="collapsible-body"><span>The command to copy data to the DGX-1 is as follows: </br>
                    </br>
                    scp -R path_to_your_data %server_account%@dgx1-request.aa.uaeu.ac.ae:/home/%server_account%/dgx-data/Task_%task_id%/ </br>
                    </br>
                    What you need:
                    <ol>
                        <li>The path to your data (On your computer, locally)</li>
                        <li>The path to the directory you want to copy the data to (On the DGX-1)</li>
                        <li>Your server account username and password</li>
                    </ol>
                </span></div>
        </li>
        <li>
            <div class="collapsible-header"><i class="material-icons">account_circle</i>What is my server account?</div>
            <div class="collapsible-body"><span>Your server account is assigned to you when the first request you submit is approved. You will recieve an email with instructions on how to proceed as well as your assigned server account</span></div>
        </li>
        <li>
            <div class="collapsible-header"><i class="material-icons">code</i>How do I use the system and what do the commands do?</div>
            <div class="collapsible-body"><span>To run your scripts directly from the terminal, use the following commands: </br>
                    </br>
                    <ol>
                        <li>ssh %server_account%@dgx1-request.aa.uaeu.ac.ae</li>
                        <li>ssh -p %ssh_port% root@localhost</li>
                    </ol> </br>
                    On your computer, type the first command into a terminal window. You will be prompted for a password. This command connects your computer to our login server. Next, type the second command into the same terminal window (once your request is approved, you will recieve an email with your designated ssh_port). This command connects your login server session to the DGX-1. You can now run your scripts from this terminal.</br>

                    </br> To run a Jupyter Notebook, use the following commands: </br>

                    </br>
                    <ol>
                        <li>ssh %server_account%@dgx1-request.aa.uaeu.ac.ae -L8888:localhost:%jupyter_port% -L6006:localhost:%tensorboard_port%</li>
                        <li>ssh -p %ssh_port% root@localhost</li>
                    </ol> </br>
                    On your computer, type the first command into a terminal window. You will be prompted for a password. This command makes the login server's ports forward to your computer's ports. You will need to make sure your computer's ports are free in order for this to work. By default, we use port 8888 for the jupyter notebook, and 6006 for the tensorboard. Next, type the second command into the same terminal window (once your request is approved, you will recieve an email with your designated ssh_port). This command connects your login server session to the DGX-1. Now go to http://localhost:%jupyter_port% (example, http://localhost:8888).

                </span></div>
        </li>
        <li>
            <div class="collapsible-header"><i class="material-icons">desktop_windows</i>How do I use Putty to connect? (Windows)</div>
            <div class="collapsible-body"><span>See the <a href="files/putty_instruction.pdf" target="_blank">attached PDF</a> for instructions on how to connect through Putty, a free SSH and telnet client for Windows. </br>
                </span></div>
        </li>
    </ul>
</section>

<script>
    $(document).ready(function() {
        $('.collapsible').collapsible();
    });
</script>

<?php include(__BASE_PATH__ . '/pages/footer.php'); ?>