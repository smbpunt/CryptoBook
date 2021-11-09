console.log('farming/form.js loaded');
import Choices from "choices.js";
import {BlockchainDapp} from "../blockchain-dapp";

new Choices('#strategy_lp_coin1');
new Choices('#strategy_lp_coin2');

BlockchainDapp('strategy_lp_blockchain', 'strategy_lp_dapp', 'id-dapp');